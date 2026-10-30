<?php

use App\Actions\DeleteProductAction;
use App\Models\Order;
use App\Models\Product;

describe('DeleteProductAction', function () {
    it('deletes a product with no orders', function () {
        $product = Product::factory()->create();

        (new DeleteProductAction())->handle($product);

        expect(Product::find($product->id))->toBeNull();
    });

    it('throws when the product has orders', function () {
        $product = Product::factory()->create();
        Order::factory()->create(['product_id' => $product->id]);

        expect(fn () => (new DeleteProductAction())->handle($product))
            ->toThrow(DomainException::class, 'This product has existing orders.');
    });

    it('keeps the product when it has orders', function () {
        $product = Product::factory()->create();
        Order::factory()->create(['product_id' => $product->id]);

        try {
            (new DeleteProductAction())->handle($product);
        } catch (DomainException) {
            // expected
        }

        expect(Product::find($product->id))->not->toBeNull();
    });

    it('leaves other products untouched', function () {
        $keep = Product::factory()->create();
        $remove = Product::factory()->create();

        (new DeleteProductAction())->handle($remove);

        expect(Product::find($keep->id))->not->toBeNull();
    });
});