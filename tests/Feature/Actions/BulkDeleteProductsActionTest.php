<?php

use App\Actions\BulkDeleteProductsAction;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

afterEach(fn () => Product::flushEventListeners());

describe('BulkDeleteProductsAction', function () {
    it('deletes all selected products when none has orders', function () {
        Product::factory()->count(3)->create();

        (new BulkDeleteProductsAction())->handle(Product::all());

        expect(Product::count())->toBe(0);
    });

    it('throws when any selected product has orders', function () {
        Product::factory()->create();
        $ordered = Product::factory()->create();
        Order::factory()->create(['product_id' => $ordered->id]);

        expect(fn () => (new BulkDeleteProductsAction())->handle(Product::all()))
            ->toThrow(DomainException::class, 'One or more selected products have existing orders.');
    });

    it('deletes nothing when any selected product has orders', function () {
        Product::factory()->create();
        $ordered = Product::factory()->create();
        Order::factory()->create(['product_id' => $ordered->id]);

        try {
            (new BulkDeleteProductsAction())->handle(Product::all());
        } catch (DomainException) {
            // expected
        }

        expect(Product::count())->toBe(2);
    });

    it('rolls back earlier deletes when a later delete fails', function () {
        $first = Product::factory()->create(['name' => 'First']);
        $second = Product::factory()->create(['name' => 'Second']);

        Product::deleting(function (Product $product) {
            if ($product->name === 'Second') {
                throw new RuntimeException('Simulated failure');
            }
        });

        try {
            (new BulkDeleteProductsAction())->handle(Collection::make([$first, $second]));
        } catch (RuntimeException) {
            // expected
        }

        expect(Product::count())->toBe(2);
    });
});