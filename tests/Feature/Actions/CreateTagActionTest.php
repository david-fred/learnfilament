<?php

use App\Actions\CreateTagAction;
use App\Models\Tag;
use Illuminate\Validation\ValidationException;

dataset('invalid tag names', [
    'name is missing' => [null],
    'name is not a string' => [12345],
    'name is over 50 characters' => [str_repeat('A', 51)],
]);

describe('CreateTagAction', function () {
    it('creates a tag with a valid name', function () {
        $tag = (new CreateTagAction())->handle(['name' => 'Electronics']);

        expect($tag)->toBeInstanceOf(Tag::class);
        expect($tag->id)->not->toBeNull();
        expect($tag->name)->toBe('Electronics');
    });

    it('saves the tag to the database', function () {
        (new CreateTagAction())->handle(['name' => 'Electronics']);

        $this->assertDatabaseHas('tags', ['name' => 'Electronics']);
    });

    it('accepts a name of exactly 50 characters', function () {
        $tag = (new CreateTagAction())->handle(['name' => str_repeat('A', 50)]);

        expect($tag->name)->toBe(str_repeat('A', 50));
    });

    it('fails when the name is already taken', function () {
        Tag::factory()->create(['name' => 'Electronics']);

        expect(fn () => (new CreateTagAction())->handle(['name' => 'Electronics']))
            ->toThrow(ValidationException::class);
    });

    it('does not save a duplicate name', function () {
        Tag::factory()->create(['name' => 'Electronics']);

        expect(fn () => (new CreateTagAction())->handle(['name' => 'Electronics']))
            ->toThrow(ValidationException::class);

        expect(Tag::where('name', 'Electronics')->count())->toBe(1);
    });

    it('rejects invalid names', function ($name) {
        expect(fn () => (new CreateTagAction())->handle(['name' => $name]))
            ->toThrow(ValidationException::class);
    })->with('invalid tag names');
});