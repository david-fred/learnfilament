<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Enums\ProductStatusEnum;

class CreateProductAction
{
    /**
     * Create a new class instance.
     */
    public function handle(array $attributes): Product
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:10'],
            'description' => ['nullable', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:1'],
            'status' => ['required', Rule::enum(ProductStatusEnum::class)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['sometimes', 'boolean']
        ])->validate();

        //$product = Product::create($validated);
        //return $product;
        return DB::transaction(fn() => Product::create($validated));
    }
}
