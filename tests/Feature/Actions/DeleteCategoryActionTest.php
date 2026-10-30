<?php

use App\Actions\DeleteCategoryAction;
use App\Models\Category;
use App\Models\Product;

describe('DeleteCategoryAction', function () {
    it('deletes a category with no products', function () {
        $category = Category::factory()->create();

        (new DeleteCategoryAction())->handle($category);

        expect(Category::find($category->id))->toBeNull();
    });

    it('throws when the category has products', function () {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        expect(fn () => (new DeleteCategoryAction())->handle($category))
            ->toThrow(DomainException::class, 'This category still has products.');
    });

    it('keeps the category when it has products', function () {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        try {
            (new DeleteCategoryAction())->handle($category);
        } catch (DomainException) {
            // expected
        }

        expect(Category::find($category->id))->not->toBeNull();
    });

    it('leaves other categories untouched', function () {
        $keep = Category::factory()->create();
        $remove = Category::factory()->create();

        (new DeleteCategoryAction())->handle($remove);

        expect(Category::find($keep->id))->not->toBeNull();
    });
});