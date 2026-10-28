<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CreateProductAction
{
    /**
     * Create a new class instance.
     */
    public function handle(array $attributes): Product
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:0']
        ])->validate();

        $product = Product::create($validated);

        return $product;
    }
}
