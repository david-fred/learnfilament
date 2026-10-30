<?php

use App\Actions\CreateOrderAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

function orderPayload(array $overrides = []): array
{
    return array_merge([
        'user_id' => User::factory()->create()->id,
        'product_id' => Product::factory()->create()->id,
        'price' => 50,
    ], $overrides);
}

dataset('invalid order attributes', [
    'user_id is missing' => [['user_id' => null]],
    'user_id does not exist' => [['user_id' => 9999]],
    'product_id is missing' => [['product_id' => null]],
    'product_id does not exist' => [['product_id' => 9999]],
    'price is missing' => [['price' => null]],
    'price is not numeric' => [['price' => 'not-a-number']],
    'price is below 10' => [['price' => 9]],
    'is_completed is not a boolean' => [['is_completed' => 'yes']],
]);

describe('CreateOrderAction', function () {
    it('creates an order with valid data', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = (new CreateOrderAction())->handle([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'price' => 50,
        ]);

        expect($order)->toBeInstanceOf(Order::class);
        expect($order->id)->not->toBeNull();
        expect($order->user_id)->toBe($user->id);
        expect($order->product_id)->toBe($product->id);
        expect($order->price)->toBe(50);
    });

    it('saves the order to the database', function () {
        $order = (new CreateOrderAction())->handle(orderPayload(['price' => 75]));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'price' => 75,
        ]);
    });

    it('defaults is_completed to false when omitted', function () {
        $order = (new CreateOrderAction())->handle(orderPayload());
        $order->refresh(); // loads the is_completed database default

        expect($order->is_completed)->toBeFalse();
    });

    it('saves is_completed as true when provided', function () {
        $order = (new CreateOrderAction())->handle(orderPayload(['is_completed' => true]));
        $order->refresh();

        expect($order->is_completed)->toBeTrue();
    });

    it('accepts a price of exactly 10', function () {
        $order = (new CreateOrderAction())->handle(orderPayload(['price' => 10]));

        expect($order->price)->toBe(10);
    });

    it('rejects invalid attributes', function (array $overrides) {
        expect(fn () => (new CreateOrderAction())->handle(orderPayload($overrides)))
            ->toThrow(ValidationException::class);
    })->with('invalid order attributes');

    it('does not save an order when validation fails', function () {
        expect(fn () => (new CreateOrderAction())->handle(orderPayload(['price' => 5])))
            ->toThrow(ValidationException::class);

        expect(Order::count())->toBe(0);
    });
});