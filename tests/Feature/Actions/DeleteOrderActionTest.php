<?php

use App\Actions\DeleteOrderAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

describe('DeleteOrderAction', function () {
    it('deletes the order', function () {
        $order = Order::factory()->create();

        (new DeleteOrderAction())->handle($order);

        expect(Order::find($order->id))->toBeNull();
    });

    it('keeps the user and product the order referenced', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        (new DeleteOrderAction())->handle($order);

        expect(User::find($user->id))->not->toBeNull();
        expect(Product::find($product->id))->not->toBeNull();
    });

    it('leaves other orders untouched', function () {
        $keep = Order::factory()->create();
        $remove = Order::factory()->create();

        (new DeleteOrderAction())->handle($remove);

        expect(Order::find($keep->id))->not->toBeNull();
    });
});