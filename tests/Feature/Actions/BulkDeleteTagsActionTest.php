<?php

use App\Actions\BulkDeleteTagsAction;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

afterEach(fn () => Tag::flushEventListeners());

describe('BulkDeleteTagsAction', function () {
    it('deletes all selected tags', function () {
        Tag::factory()->count(3)->create();

        (new BulkDeleteTagsAction())->handle(Tag::all());

        expect(Tag::count())->toBe(0);
    });

    it('leaves unselected tags untouched', function () {
        $keep = Tag::factory()->create();
        $selected = Tag::factory()->count(2)->create();

        (new BulkDeleteTagsAction())->handle(Collection::make($selected));

        expect(Tag::find($keep->id))->not->toBeNull();
        expect(Tag::count())->toBe(1);
    });

    it('rolls back earlier deletes when a later delete fails', function () {
        $first = Tag::factory()->create(['name' => 'First']);
        $second = Tag::factory()->create(['name' => 'Second']);

        Tag::deleting(function (Tag $tag) {
            if ($tag->name === 'Second') {
                throw new RuntimeException('Simulated failure');
            }
        });

        try {
            (new BulkDeleteTagsAction())->handle(Collection::make([$first, $second]));
        } catch (RuntimeException) {
            // expected
        }

        expect(Tag::count())->toBe(2);
    });
});