<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateOrderAction
{
    public function handle(array $attributes): Order
    {
        $validated = Validator::make($attributes, [
            'user_id' => ['required', 'exists:users,id'],
            'product_id' => ['required', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:10'],
            'is_completed' => ['sometimes', 'boolean'],
        ])->validate();

        return DB::transaction(fn() => Order::create($validated));
    }
}
