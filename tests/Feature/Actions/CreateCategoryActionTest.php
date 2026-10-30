<?php

use App\Actions\CreateCategoryAction;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

describe('CreateCategoryAction', function () {
    it('creates a category with valid data', function () {
        $category = (new CreateCategoryAction())->handle(['name' => 'Electronics']);

        expect($category)->toBeInstanceOf(Category::class);
        expect($category->id)->not->toBeNull();
        expect($category->name)->toBe('Electronics');
        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    });

    it('fails when name is missing or empty', function () {
        expect(fn () => (new CreateCategoryAction())->handle(['name' => '']))
            ->toThrow(ValidationException::class);
    });

    it('fails when name is not a string', function () {
        expect(fn () => (new CreateCategoryAction())->handle(['name' => 12345]))
            ->toThrow(ValidationException::class);
    });

    it('fails when name exceeds 100 characters', function () {
        expect(fn () => (new CreateCategoryAction())->handle(['name' => str_repeat('a', 101)]))
            ->toThrow(ValidationException::class);
    });

    it('accepts a name of exactly 100 characters', function () {
        $name = str_repeat('a', 100);

        $category = (new CreateCategoryAction())->handle(['name' => $name]);

        expect($category->name)->toBe($name);
        $this->assertDatabaseHas('categories', ['name' => $name]);
    });

    it('fails when the name is already taken', function () {
        (new CreateCategoryAction())->handle(['name' => 'Unique Category']);

        expect(fn () => (new CreateCategoryAction())->handle(['name' => 'Unique Category']))
            ->toThrow(ValidationException::class);
    });

    it('does not write a duplicate when the name is taken', function () {
        (new CreateCategoryAction())->handle(['name' => 'Unique Category']);

        try {
            (new CreateCategoryAction())->handle(['name' => 'Unique Category']);
        } catch (ValidationException) {
            // expected
        }

        expect(Category::where('name', 'Unique Category')->count())->toBe(1);
    });
});