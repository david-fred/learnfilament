<?php

namespace App\Actions;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CreateCategoryAction
{
    public function handle(array $attributes): Category
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:100', 'unique:categories'],
        ])->validate();

        return Category::create($validated);
    }
}