<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BulkDeleteTagsAction
{
    public function handle(Collection $tags): void
    {
        DB::transaction(function () use ($tags) {
            foreach ($tags as $tag) {
                $tag->delete();
            }
        });
    }
}