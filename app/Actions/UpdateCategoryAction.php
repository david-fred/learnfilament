<?php

namespace App\Actions;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateCategoryAction
{
    public function handle(Category $category, array $attributes): Category
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($category->id)],
        ])->validate();

        $category->update($validated);

        return $category;
    }
}