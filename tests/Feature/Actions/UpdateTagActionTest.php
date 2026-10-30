<?php

use App\Actions\UpdateTagAction;
use App\Models\Tag;
use Illuminate\Validation\ValidationException;

dataset('invalid update tag names', [
    'name is missing' => [null],
    'name is not a string' => [12345],
    'name is over 50 characters' => [str_repeat('A', 51)],
]);

describe('UpdateTagAction', function () {
    it('updates the name and returns the same tag', function () {
        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $updated = (new UpdateTagAction())->handle($tag, ['name' => 'New Name']);

        expect($updated)->toBeInstanceOf(Tag::class);
        expect($updated->id)->toBe($tag->id);
        expect($updated->name)->toBe('New Name');
    });

    it('saves the new name to the database', function () {
        $tag = Tag::factory()->create(['name' => 'Old Name']);

        (new UpdateTagAction())->handle($tag, ['name' => 'New Name']);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
        ]);
    });

    it('does not create a new tag', function () {
        $tag = Tag::factory()->create(['name' => 'Old Name']);

        (new UpdateTagAction())->handle($tag, ['name' => 'New Name']);

        expect(Tag::count())->toBe(1);
    });

    it('allows keeping its own current name', function () {
        $tag = Tag::factory()->create(['name' => 'Electronics']);

        $updated = (new UpdateTagAction())->handle($tag, ['name' => 'Electronics']);

        expect($updated->name)->toBe('Electronics');
    });

    it('fails when the name belongs to another tag', function () {
        Tag::factory()->create(['name' => 'Taken']);
        $tag = Tag::factory()->create(['name' => 'Original']);

        expect(fn () => (new UpdateTagAction())->handle($tag, ['name' => 'Taken']))
            ->toThrow(ValidationException::class);
    });

    it('keeps the original name when the new name is taken', function () {
        Tag::factory()->create(['name' => 'Taken']);
        $tag = Tag::factory()->create(['name' => 'Original']);

        expect(fn () => (new UpdateTagAction())->handle($tag, ['name' => 'Taken']))
            ->toThrow(ValidationException::class);

        expect($tag->refresh()->name)->toBe('Original');
    });

    it('accepts a name of exactly 50 characters', function () {
        $tag = Tag::factory()->create(['name' => 'Original']);

        $updated = (new UpdateTagAction())->handle($tag, ['name' => str_repeat('A', 50)]);

        expect($updated->name)->toBe(str_repeat('A', 50));
    });

    it('rejects invalid names', function ($name) {
        $tag = Tag::factory()->create(['name' => 'Original']);

        expect(fn () => (new UpdateTagAction())->handle($tag, ['name' => $name]))
            ->toThrow(ValidationException::class);
    })->with('invalid update tag names');
});