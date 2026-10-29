<?php

namespace App\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateCustomerAction
{

    /**
     * Create a new class instance.
     */
    public function handle(array $attributes): Customer
    {
        $validated = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'unique:customers']
        ])->validate();

        return DB::transaction(fn() => Customer::create($validated));
    }
}
