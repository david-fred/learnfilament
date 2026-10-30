<?php

namespace App\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class CreateCustomerAction
{
    public function handle(array $attributes): Customer
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'unique:customers'],
        ])->validate();

        return Customer::create($validated);
    }
}