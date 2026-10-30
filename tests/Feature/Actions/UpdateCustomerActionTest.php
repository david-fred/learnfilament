<?php

use App\Actions\UpdateCustomerAction;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

function updateCustomerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ], $overrides);
}

dataset('invalid update customer attributes', [
    'name is missing' => [['name' => null]],
    'name is not a string' => [['name' => 12345]],
    'name is over 150 characters' => [['name' => str_repeat('a', 151)]],
    'email is missing' => [['email' => null]],
    'email is not a string' => [['email' => 12345]],
    'email format is invalid' => [['email' => 'invalid-email']],
]);

describe('UpdateCustomerAction', function () {
    it('updates name and email and returns the same customer', function () {
        $customer = Customer::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $updated = (new UpdateCustomerAction())->handle($customer, updateCustomerPayload());

        expect($updated)->toBeInstanceOf(Customer::class);
        expect($updated->id)->toBe($customer->id);
        expect($updated->name)->toBe('Updated Name');
        expect($updated->email)->toBe('updated@example.com');
    });

    it('saves the changes to the database', function () {
        $customer = Customer::factory()->create();

        (new UpdateCustomerAction())->handle($customer, updateCustomerPayload());

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    });

    it('allows keeping its own email while changing the name', function () {
        $customer = Customer::factory()->create(['email' => 'same@example.com']);

        $updated = (new UpdateCustomerAction())->handle($customer, updateCustomerPayload([
            'email' => 'same@example.com',
        ]));

        expect($updated->email)->toBe('same@example.com');
    });

    it('fails when the email belongs to another customer', function () {
        Customer::factory()->create(['email' => 'taken@example.com']);
        $customer = Customer::factory()->create(['email' => 'original@example.com']);

        expect(fn () => (new UpdateCustomerAction())->handle($customer, updateCustomerPayload([
            'email' => 'taken@example.com',
        ])))->toThrow(ValidationException::class);
    });

    it('keeps the original email when the new email is taken', function () {
        Customer::factory()->create(['email' => 'taken@example.com']);
        $customer = Customer::factory()->create(['email' => 'original@example.com']);

        expect(fn () => (new UpdateCustomerAction())->handle($customer, updateCustomerPayload([
            'email' => 'taken@example.com',
        ])))->toThrow(ValidationException::class);

        expect($customer->refresh()->email)->toBe('original@example.com');
    });

    it('accepts a name of exactly 150 characters', function () {
        $customer = Customer::factory()->create();

        $updated = (new UpdateCustomerAction())->handle($customer, updateCustomerPayload([
            'name' => str_repeat('a', 150),
        ]));

        expect($updated->name)->toBe(str_repeat('a', 150));
    });

    it('rejects invalid attributes', function (array $overrides) {
        $customer = Customer::factory()->create();

        expect(fn () => (new UpdateCustomerAction())->handle($customer, updateCustomerPayload($overrides)))
            ->toThrow(ValidationException::class);
    })->with('invalid update customer attributes');
});