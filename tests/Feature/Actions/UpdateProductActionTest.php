<?php

use App\Actions\UpdateProductAction;
use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

function updateProductPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Updated Product',
        'price' => 7500,
        'description' => 'Updated description',
        'amount' => 20,
        'status' => ProductStatusEnum::OUT_OF_STOCK->value,
        'category_id' => null,
        'is_active' => false,
    ], $overrides);
}

function createExistingProduct(array $overrides = []): Product
{
    return Product::factory()->create(array_merge([
        'name' => 'Original Product',
        'price' => 5000,
        'description' => 'Original description',
        'amount' => 10,
        'status' => ProductStatusEnum::IN_STOCK->value,
        'category_id' => null,
        'is_active' => true,
    ], $overrides));
}

dataset('invalid update product attributes', [
    'name is missing' => [['name' => null]],
    'name is not a string' => [['name' => 12345]],
    'name is over 150 characters' => [['name' => str_repeat('A', 151)]],
    'price is missing' => [['price' => null]],
    'price is not numeric' => [['price' => 'not-a-number']],
    'price is below 10' => [['price' => 9]],
    'description is over 150 characters' => [['description' => str_repeat('A', 151)]],
    'amount is missing' => [['amount' => null]],
    'amount is not numeric' => [['amount' => 'not-a-number']],
    'amount is below 1' => [['amount' => 0]],
    'status is missing' => [['status' => null]],
    'status is not a valid enum value' => [['status' => 'invalid-status']],
    'category does not exist' => [['category_id' => 99999]],
    'is_active is not a boolean' => [['is_active' => 'yes']],
]);

describe('UpdateProductAction', function () {
    it('updates every field and returns the same product', function () {
        $product = createExistingProduct();

        $updated = (new UpdateProductAction())->handle($product, updateProductPayload());

        expect($updated)->toBeInstanceOf(Product::class);
        expect($updated->id)->toBe($product->id);
        expect($updated->name)->toBe('Updated Product');
        expect($updated->price)->toBe(7500);
        expect($updated->description)->toBe('Updated description');
        expect($updated->amount)->toBe(20);
        expect($updated->status)->toBe(ProductStatusEnum::OUT_OF_STOCK);
        expect($updated->is_active)->toBeFalse();
    });

    it('saves the changes to the database', function () {
        $product = createExistingProduct();

        (new UpdateProductAction())->handle($product, updateProductPayload());

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 7500,
        ]);
    });

    it('does not create a new product', function () {
        $product = createExistingProduct();

        (new UpdateProductAction())->handle($product, updateProductPayload());

        expect(Product::count())->toBe(1);
    });

    it('moves the product to a new category', function () {
        $category = Category::factory()->create();
        $product = createExistingProduct();

        $updated = (new UpdateProductAction())->handle($product, updateProductPayload([
            'category_id' => $category->id,
        ]));

        expect($updated->category_id)->toBe($category->id);
    });

    it('clears the category when category_id is null', function () {
        $category = Category::factory()->create();
        $product = createExistingProduct(['category_id' => $category->id]);

        $updated = (new UpdateProductAction())->handle($product, updateProductPayload([
            'category_id' => null,
        ]));

        expect($updated->category_id)->toBeNull();
    });

    it('keeps is_active unchanged when it is omitted', function () {
        $product = createExistingProduct(['is_active' => true]);

        $data = updateProductPayload();
        unset($data['is_active']);

        $updated = (new UpdateProductAction())->handle($product, $data);

        expect($updated->is_active)->toBeTrue();
    });

    it('accepts a name of exactly 150 characters', function () {
        $product = createExistingProduct();

        $updated = (new UpdateProductAction())->handle($product, updateProductPayload([
            'name' => str_repeat('A', 150),
        ]));

        expect($updated->name)->toBe(str_repeat('A', 150));
    });

    it('accepts a price of exactly 10', function () {
        $product = createExistingProduct();

        $updated = (new UpdateProductAction())->handle($product, updateProductPayload(['price' => 10]));

        expect($updated->price)->toBe(10);
    });

    it('accepts a null description', function () {
        $product = createExistingProduct();

        $updated = (new UpdateProductAction())->handle($product, updateProductPayload([
            'description' => null,
        ]));

        expect($updated->description)->toBeNull();
    });

    it('rejects invalid attributes', function (array $overrides) {
        $product = createExistingProduct();

        expect(fn () => (new UpdateProductAction())->handle($product, updateProductPayload($overrides)))
            ->toThrow(ValidationException::class);
    })->with('invalid update product attributes');

    it('keeps the original values when validation fails', function () {
        $product = createExistingProduct();

        expect(fn () => (new UpdateProductAction())->handle($product, updateProductPayload(['price' => 1])))
            ->toThrow(ValidationException::class);

        $product->refresh();
        expect($product->name)->toBe('Original Product');
        expect($product->price)->toBe(5000);
    });
});