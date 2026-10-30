<?php

namespace App\Actions;

use App\Models\Tag;

class DeleteTagAction
{
    public function handle(Tag $tag): void
    {
        $tag->delete();
    }
}