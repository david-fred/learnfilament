<?php

use App\Actions\UpdateOrderAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

function updateOrderPayload(array $overrides = []): array
{
    return array_merge([
        'user_id' => User::factory()->create()->id,
        'product_id' => Product::factory()->create()->id,
        'price' => 100,
    ], $overrides);
}

dataset('invalid update order attributes', [
    'user_id is missing' => [['user_id' => null]],
    'user_id does not exist' => [['user_id' => 9999]],
    'product_id is missing' => [['product_id' => null]],
    'product_id does not exist' => [['product_id' => 9999]],
    'price is missing' => [['price' => null]],
    'price is not numeric' => [['price' => 'not-a-number']],
    'price is below 10' => [['price' => 9]],
    'is_completed is not a boolean' => [['is_completed' => 'yes']],
]);

describe('UpdateOrderAction', function () {
    it('updates the order and returns the same instance', function () {
        $order = Order::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $updated = (new UpdateOrderAction())->handle($order, [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'price' => 75,
        ]);

        expect($updated)->toBeInstanceOf(Order::class);
        expect($updated->id)->toBe($order->id);
        expect($updated->user_id)->toBe($user->id);
        expect($updated->product_id)->toBe($product->id);
        expect($updated->price)->toBe(75);
    });

    it('saves the changes to the database', function () {
        $order = Order::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        (new UpdateOrderAction())->handle($order, [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'price' => 125,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'price' => 125,
        ]);
    });

    it('sets is_completed when provided', function () {
        $order = Order::factory()->create(['is_completed' => false]);

        (new UpdateOrderAction())->handle($order, updateOrderPayload(['is_completed' => true]));

        expect($order->refresh()->is_completed)->toBeTrue();
    });

    it('keeps is_completed unchanged when omitted', function () {
        $order = Order::factory()->create(['is_completed' => true]);

        (new UpdateOrderAction())->handle($order, updateOrderPayload());

        expect($order->refresh()->is_completed)->toBeTrue();
    });

    it('accepts a price of exactly 10', function () {
        $order = Order::factory()->create();

        $updated = (new UpdateOrderAction())->handle($order, updateOrderPayload(['price' => 10]));

        expect($updated->price)->toBe(10);
    });

    it('rejects invalid attributes', function (array $overrides) {
        $order = Order::factory()->create();

        expect(fn () => (new UpdateOrderAction())->handle($order, updateOrderPayload($overrides)))
            ->toThrow(ValidationException::class);
    })->with('invalid update order attributes');

    it('keeps the original price when validation fails', function () {
        $order = Order::factory()->create(['price' => 100]);

        expect(fn () => (new UpdateOrderAction())->handle($order, updateOrderPayload(['price' => 5])))
            ->toThrow(ValidationException::class);

        expect($order->refresh()->price)->toBe(100);
    });
});