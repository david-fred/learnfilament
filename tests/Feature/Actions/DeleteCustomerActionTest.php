<?php

use App\Actions\DeleteCustomerAction;
use App\Models\Customer;

describe('DeleteCustomerAction', function () {
    it('deletes the customer', function () {
        $customer = Customer::factory()->create();

        (new DeleteCustomerAction())->handle($customer);

        expect(Customer::find($customer->id))->toBeNull();
    });

    it('leaves other customers untouched', function () {
        $keep = Customer::factory()->create();
        $remove = Customer::factory()->create();

        (new DeleteCustomerAction())->handle($remove);

        expect(Customer::find($keep->id))->not->toBeNull();
    });
});