<?php

use App\Actions\BulkDeleteCategoriesAction;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

afterEach(fn () => Category::flushEventListeners());

describe('BulkDeleteCategoriesAction', function () {
    it('deletes all selected categories when none has products', function () {
        Category::factory()->count(3)->create();

        (new BulkDeleteCategoriesAction())->handle(Category::all());

        expect(Category::count())->toBe(0);
    });

    it('throws when any selected category has products', function () {
        Category::factory()->create();
        $stocked = Category::factory()->create();
        Product::factory()->create(['category_id' => $stocked->id]);

        expect(fn () => (new BulkDeleteCategoriesAction())->handle(Category::all()))
            ->toThrow(DomainException::class, 'One or more selected categories still have products.');
    });

    it('deletes nothing when any selected category has products', function () {
        Category::factory()->create();
        $stocked = Category::factory()->create();
        Product::factory()->create(['category_id' => $stocked->id]);

        try {
            (new BulkDeleteCategoriesAction())->handle(Category::all());
        } catch (DomainException) {
            // expected
        }

        expect(Category::count())->toBe(2);
    });

    it('rolls back earlier deletes when a later delete fails', function () {
        $first = Category::factory()->create(['name' => 'First']);
        $second = Category::factory()->create(['name' => 'Second']);

        Category::deleting(function (Category $category) {
            if ($category->name === 'Second') {
                throw new RuntimeException('Simulated failure');
            }
        });

        try {
            (new BulkDeleteCategoriesAction())->handle(Collection::make([$first, $second]));
        } catch (RuntimeException) {
            // expected
        }

        expect(Category::count())->toBe(2);
    });
});