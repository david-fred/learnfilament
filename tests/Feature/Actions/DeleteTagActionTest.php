<?php

use App\Actions\DeleteTagAction;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

describe('DeleteTagAction', function () {
    it('deletes the tag', function () {
        $tag = Tag::factory()->create();

        (new DeleteTagAction())->handle($tag);

        expect(Tag::find($tag->id))->toBeNull();
    });

    it('removes the tag from products', function () {
        $product = Product::factory()->create();
        $tag = Tag::factory()->create();
        $product->tags()->attach($tag);

        (new DeleteTagAction())->handle($tag);

        expect(DB::table('product_tag')->where('tag_id', $tag->id)->exists())->toBeFalse();
    });

    it('keeps the products that had the tag', function () {
        $product = Product::factory()->create();
        $tag = Tag::factory()->create();
        $product->tags()->attach($tag);

        (new DeleteTagAction())->handle($tag);

        expect(Product::find($product->id))->not->toBeNull();
    });

    it('leaves other tags untouched', function () {
        $keep = Tag::factory()->create();
        $remove = Tag::factory()->create();

        (new DeleteTagAction())->handle($remove);

        expect(Tag::find($keep->id))->not->toBeNull();
    });
});