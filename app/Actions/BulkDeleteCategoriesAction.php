<?php

namespace App\Actions;

use App\Models\Category;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BulkDeleteCategoriesAction
{
    public function handle(Collection $categories): void
    {
        if ($categories->contains(fn (Category $category) => $category->products()->exists())) {
            throw new DomainException('One or more selected categories still have products.');
        }

        DB::transaction(function () use ($categories) {
            foreach ($categories as $category) {
                $category->delete();
            }
        });
    }
}