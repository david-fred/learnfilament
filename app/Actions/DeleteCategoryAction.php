<?php

namespace App\Actions;

use App\Models\Category;
use DomainException;

class DeleteCategoryAction
{
    public function handle(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new DomainException('This category still has products.');
        }

        $category->delete();
    }
}