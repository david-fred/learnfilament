<?php

use App\Actions\CreateCustomerAction;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

function customerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ], $overrides);
}

dataset('invalid customer attributes', [
    'name is missing' => [['name' => null]],
    'name is not a string' => [['name' => 12345]],
    'name is over 150 characters' => [['name' => str_repeat('a', 151)]],
    'email is missing' => [['email' => null]],
    'email is not a string' => [['email' => 12345]],
    'email format is invalid' => [['email' => 'invalid-email']],
]);

describe('CreateCustomerAction', function () {
    it('creates a customer with valid data', function () {
        $customer = (new CreateCustomerAction())->handle([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $customer->refresh(); // loads the is_verified database default

        expect($customer)->toBeInstanceOf(Customer::class);
        expect($customer->id)->not->toBeNull();
        expect($customer->name)->toBe('John Doe');
        expect($customer->email)->toBe('john@example.com');
        expect($customer->is_verified)->toBeFalse();
    });

    it('saves the customer to the database', function () {
        (new CreateCustomerAction())->handle(customerPayload([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]));

        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);
    });

    it('accepts a name of exactly 150 characters', function () {
        $name = str_repeat('a', 150);

        $customer = (new CreateCustomerAction())->handle(customerPayload(['name' => $name]));

        expect($customer->name)->toBe($name);
    });

    it('accepts a valid email with plus sign and subdomain', function () {
        $customer = (new CreateCustomerAction())->handle(customerPayload([
            'email' => 'valid.email+tag@subdomain.example.com',
        ]));

        expect($customer->email)->toBe('valid.email+tag@subdomain.example.com');
    });

    it('fails when the email is already taken', function () {
        Customer::factory()->create(['email' => 'existing@example.com']);

        expect(fn () => (new CreateCustomerAction())->handle(customerPayload([
            'email' => 'existing@example.com',
        ])))->toThrow(ValidationException::class);
    });

    it('does not save a duplicate email', function () {
        Customer::factory()->create(['email' => 'existing@example.com']);

        expect(fn () => (new CreateCustomerAction())->handle(customerPayload([
            'email' => 'existing@example.com',
        ])))->toThrow(ValidationException::class);

        expect(Customer::where('email', 'existing@example.com')->count())->toBe(1);
    });

    it('rejects invalid attributes', function (array $overrides) {
        expect(fn () => (new CreateCustomerAction())->handle(customerPayload($overrides)))
            ->toThrow(ValidationException::class);
    })->with('invalid customer attributes');
});