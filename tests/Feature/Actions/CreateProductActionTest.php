<?php

use App\Actions\CreateProductAction;
use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\ValidationException;

function createProductPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test Product',
        'price' => 5000,
        'amount' => 10,
        'status' => ProductStatusEnum::IN_STOCK->value,
    ], $overrides);
}

dataset('invalid product attributes', [
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

describe('CreateProductAction', function () {
    it('creates a product with all fields', function () {
        $category = Category::factory()->create();

        $product = (new CreateProductAction())->handle(createProductPayload([
            'description' => 'A test product',
            'category_id' => $category->id,
            'is_active' => true,
        ]));

        expect($product)->toBeInstanceOf(Product::class);
        expect($product->id)->not->toBeNull();
        expect($product->name)->toBe('Test Product');
        expect($product->price)->toBe(5000);
        expect($product->description)->toBe('A test product');
        expect($product->amount)->toBe(10);
        expect($product->status)->toBe(ProductStatusEnum::IN_STOCK);
        expect($product->category_id)->toBe($category->id);
        expect($product->is_active)->toBeTrue();
    });

    it('creates a product with only required fields and applies defaults', function () {
        $product = (new CreateProductAction())->handle([
            'name' => 'Minimal Product',
            'price' => 10,
            'amount' => 1,
            'status' => ProductStatusEnum::COMING_SOON->value,
        ]);

        expect($product->description)->toBeNull();
        expect($product->category_id)->toBeNull();
        expect($product->is_active)->toBeTrue(); // database default, loaded by refresh()
    });

    it('saves the product to the database', function () {
        $product = (new CreateProductAction())->handle(createProductPayload([
            'name' => 'Database Test Product',
        ]));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Database Test Product',
        ]);
    });

    it('accepts a name of exactly 150 characters', function () {
        $product = (new CreateProductAction())->handle(createProductPayload([
            'name' => str_repeat('A', 150),
        ]));

        expect($product->name)->toBe(str_repeat('A', 150));
    });

    it('accepts a description of exactly 150 characters', function () {
        $product = (new CreateProductAction())->handle(createProductPayload([
            'description' => str_repeat('A', 150),
        ]));

        expect($product->description)->toBe(str_repeat('A', 150));
    });

    it('accepts a null description', function () {
        $product = (new CreateProductAction())->handle(createProductPayload(['description' => null]));

        expect($product->description)->toBeNull();
    });

    it('accepts a price of exactly 10', function () {
        $product = (new CreateProductAction())->handle(createProductPayload(['price' => 10]));

        expect($product->price)->toBe(10);
    });

    it('accepts an amount of exactly 1', function () {
        $product = (new CreateProductAction())->handle(createProductPayload(['amount' => 1]));

        expect($product->amount)->toBe(1);
    });

    it('accepts every ProductStatusEnum value', function () {
        foreach (ProductStatusEnum::cases() as $status) {
            $product = (new CreateProductAction())->handle(createProductPayload([
                'name' => "Product {$status->value}",
                'status' => $status->value,
            ]));

            expect($product->status)->toBe($status);
        }
    });

    it('accepts a valid category_id', function () {
        $category = Category::factory()->create();

        $product = (new CreateProductAction())->handle(createProductPayload([
            'category_id' => $category->id,
        ]));

        expect($product->category_id)->toBe($category->id);
    });

    it('accepts a null category_id', function () {
        $product = (new CreateProductAction())->handle(createProductPayload(['category_id' => null]));

        expect($product->category_id)->toBeNull();
    });

    it('stores is_active as false when provided', function () {
        $product = (new CreateProductAction())->handle(createProductPayload(['is_active' => false]));

        expect($product->is_active)->toBeFalse();
    });

    it('rejects invalid attributes', function (array $overrides) {
        expect(fn () => (new CreateProductAction())->handle(createProductPayload($overrides)))
            ->toThrow(ValidationException::class);
    })->with('invalid product attributes');
});