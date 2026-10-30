<?php

use App\Actions\UpdateCategoryAction;
use App\Models\Category;
use Illuminate\Validation\ValidationException;

dataset('invalid update category names', [
    'name is missing' => [null],
    'name is not a string' => [12345],
    'name is over 100 characters' => [str_repeat('A', 101)],
]);

describe('UpdateCategoryAction', function () {
    it('updates the name and returns the same category', function () {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $updated = (new UpdateCategoryAction())->handle($category, ['name' => 'New Name']);

        expect($updated)->toBeInstanceOf(Category::class);
        expect($updated->id)->toBe($category->id);
        expect($updated->name)->toBe('New Name');
    });

    it('saves the new name to the database', function () {
        $category = Category::factory()->create(['name' => 'Old Name']);

        (new UpdateCategoryAction())->handle($category, ['name' => 'New Name']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
        ]);
    });

    it('does not create a new category', function () {
        $category = Category::factory()->create(['name' => 'Old Name']);

        (new UpdateCategoryAction())->handle($category, ['name' => 'New Name']);

        expect(Category::count())->toBe(1);
    });

    it('allows keeping its own current name', function () {
        $category = Category::factory()->create(['name' => 'Electronics']);

        $updated = (new UpdateCategoryAction())->handle($category, ['name' => 'Electronics']);

        expect($updated->name)->toBe('Electronics');
    });

    it('does not change other categories', function () {
        $other = Category::factory()->create(['name' => 'Other']);
        $category = Category::factory()->create(['name' => 'Original']);

        (new UpdateCategoryAction())->handle($category, ['name' => 'Renamed']);

        expect($other->refresh()->name)->toBe('Other');
    });

    it('fails when the name belongs to another category', function () {
        Category::factory()->create(['name' => 'Taken']);
        $category = Category::factory()->create(['name' => 'Original']);

        expect(fn () => (new UpdateCategoryAction())->handle($category, ['name' => 'Taken']))
            ->toThrow(ValidationException::class);
    });

    it('keeps the original name when the new name is taken', function () {
        Category::factory()->create(['name' => 'Taken']);
        $category = Category::factory()->create(['name' => 'Original']);

        expect(fn () => (new UpdateCategoryAction())->handle($category, ['name' => 'Taken']))
            ->toThrow(ValidationException::class);

        expect($category->refresh()->name)->toBe('Original');
    });

    it('accepts a name of exactly 100 characters', function () {
        $category = Category::factory()->create(['name' => 'Original']);

        $updated = (new UpdateCategoryAction())->handle($category, ['name' => str_repeat('A', 100)]);

        expect($updated->name)->toBe(str_repeat('A', 100));
    });

    it('rejects invalid names', function ($name) {
        $category = Category::factory()->create(['name' => 'Original']);

        expect(fn () => (new UpdateCategoryAction())->handle($category, ['name' => $name]))
            ->toThrow(ValidationException::class);
    })->with('invalid update category names');
});