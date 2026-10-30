<?php

namespace App\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateCustomerAction
{
    public function handle(Customer $customer, array $attributes): Customer
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', Rule::unique('customers')->ignore($customer->id)],
        ])->validate();

        $customer->update($validated);

        return $customer;
    }
}