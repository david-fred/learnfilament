<?php

namespace App\Actions;

use App\Models\Customer;

class DeleteCustomerAction
{
    public function handle(Customer $customer): void
    {
        $customer->delete();
    }
}