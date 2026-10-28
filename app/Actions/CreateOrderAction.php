<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Facades\Validator;

class CreateOrderAction
{
    public function handle(array $attributes): Order
    {
        $validated = Validator::make($attributes, [
            'price' => ['required', 'numeric', 'min:10']
        ])->validate();

        return Order::create($validated);
    }
}
