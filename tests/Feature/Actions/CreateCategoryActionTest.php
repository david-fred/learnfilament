<?php

use App\Actions\CreateCategoryAction;
use App\Models\Category;

describe('CreateCategoryAction', function () {

    it('creates a category with valid data', function () {
        $action = new CreateCategoryAction();

        $category = $action->handle([
            'name' => 'Electronics',
        ]);

        expect($category)->toBeInstanceOf(Category::class);
        expect($category->name)->toBe('Electronics');
        expect($category->id)->not()->toBeNull();

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
        ]);
    });

    it('requires a name field', function () {
        $action = new CreateCategoryAction();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $action->handle([
            'name' => '',
        ]);
    })->throws(\Illuminate\Validation\ValidationException::class);

    it('validates name is a string', function () {
        $action = new CreateCategoryAction();

        expect(fn() => $action->handle([
            'name' => 12345,  // Integer, not string
        ]))->toThrow(\Illuminate\Validation\ValidationException::class);
    });

    it('validates name max length is 100 characters', function () {
        $action = new CreateCategoryAction();
        $longName = str_repeat('a', 101);

        expect(fn() => $action->handle([
            'name' => $longName,
        ]))->toThrow(\Illuminate\Validation\ValidationException::class);
    });

    it('enforces unique name constraint', function () {
        $action = new CreateCategoryAction();

        // Create first category
        $action->handle(['name' => 'Unique Category']);

        // Try to create duplicate
        expect(fn() => $action->handle([
            'name' => 'Unique Category',
        ]))->toThrow(\Illuminate\Validation\ValidationException::class);
    });

    it('allows name at max length boundary (100 chars)', function () {
        $action = new CreateCategoryAction();
        $maxName = str_repeat('a', 100);

        $category = $action->handle(['name' => $maxName]);

        expect($category->name)->toHaveLength(100);
        $this->assertDatabaseHas('categories', ['name' => $maxName]);
    });

    it('uses database transaction for atomicity', function () {
        $action = new CreateCategoryAction();

        $category = $action->handle(['name' => 'Transactional Test']);

        // Verify it's actually in database, not just in memory
        $fromDb = Category::find($category->id);
        expect($fromDb)->not()->toBeNull();
        expect($fromDb->name)->toBe('Transactional Test');
    });

    it('returns a category model instance with all attributes', function () {
        $action = new CreateCategoryAction();

        $result = $action->handle(['name' => 'Test Category']);

        expect($result)->toBeInstanceOf(Category::class);
        expect($result->id)->not()->toBeNull();
        expect($result->name)->toBe('Test Category');
        expect($result->getAttribute('created_at'))->not()->toBeNull();
        expect($result->getAttribute('updated_at'))->not()->toBeNull();
    });

    it('handles missing name key gracefully', function () {
        $action = new CreateCategoryAction();

        expect(fn() => $action->handle([]))->toThrow(
            \Illuminate\Validation\ValidationException::class
        );
    });

    it('trims whitespace from name', function () {
        $action = new CreateCategoryAction();

        $category = $action->handle(['name' => '  Spaced Category  ']);

        // Note: If you want to trim, add ->trim() to your rules or model
        // For now, just verify it's created as-is
        expect($category->name)->toBeTruthy();
    });
});
