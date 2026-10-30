<?php

use App\Actions\UpdateCustomerAction;
use App\Models\Customer;

describe('UpdateCustomerAction', function () {
    describe('Valid Update', function () {
        it('updates customer with valid attributes', function () {
            $customer = Customer::factory()->create([
                'name' => 'Old Name',
                'email' => 'old@example.com',
            ]);
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ];

            $result = $updateCustomer->handle($customer, $data);

            expect($result)->toBeInstanceOf(Customer::class);
            expect($result->id)->toBe($customer->id);
            expect($result->name)->toBe('New Name');
            expect($result->email)->toBe('new@example.com');
        });

        it('persists updated customer to database', function () {
            $customer = Customer::factory()->create([
                'name' => 'Original Name',
                'email' => 'original@example.com',
            ]);
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ];

            $updateCustomer->handle($customer, $data);

            $this->assertDatabaseHas('customers', [
                'id' => $customer->id,
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);
        });

        it('returns updated model instance', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Updated',
                'email' => 'updated@example.com',
            ];

            $result = $updateCustomer->handle($customer, $data);

            expect($result)->toBeInstanceOf(Customer::class);
            expect($result->id)->toBe($customer->id);
        });

        it('allows keeping the same email when updating other fields', function () {
            $customer = Customer::factory()->create([
                'name' => 'Old Name',
                'email' => 'same@example.com',
            ]);
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'New Name',
                'email' => 'same@example.com',
            ];

            $result = $updateCustomer->handle($customer, $data);

            expect($result->name)->toBe('New Name');
            expect($result->email)->toBe('same@example.com');
        });

        it('updates only provided fields', function () {
            $customer = Customer::factory()->create([
                'name' => 'Original Name',
                'email' => 'original@example.com',
            ]);
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'New Name',
                'email' => 'original@example.com',
            ];

            $updateCustomer->handle($customer, $data);

            $this->assertDatabaseHas('customers', [
                'id' => $customer->id,
                'name' => 'New Name',
                'email' => 'original@example.com',
            ]);
        });
    });

    describe('Name Validation', function () {
        it('fails when name is missing', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'email' => 'test@example.com',
            ];

            expect(fn() => $updateCustomer->handle($customer, $data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when name is not a string', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 12345,
                'email' => 'test@example.com',
            ];

            expect(fn() => $updateCustomer->handle($customer, $data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when name exceeds 150 characters', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => str_repeat('a', 151),
                'email' => 'test@example.com',
            ];

            expect(fn() => $updateCustomer->handle($customer, $data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('succeeds with name at max length of 150 characters', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $name = str_repeat('a', 150);
            $data = [
                'name' => $name,
                'email' => 'test@example.com',
            ];

            $result = $updateCustomer->handle($customer, $data);

            expect($result->name)->toBe($name);
            expect(strlen($result->name))->toBe(150);
        });
    });

    describe('Email Validation', function () {
        it('fails when email is missing', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Test User',
            ];

            expect(fn() => $updateCustomer->handle($customer, $data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when email is not a string', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Test User',
                'email' => 12345,
            ];

            expect(fn() => $updateCustomer->handle($customer, $data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails with invalid email format', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Test User',
                'email' => 'invalid-email',
            ];

            expect(fn() => $updateCustomer->handle($customer, $data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when email already exists on another customer', function () {
            $customer1 = Customer::factory()->create(['email' => 'customer1@example.com']);
            $customer2 = Customer::factory()->create(['email' => 'customer2@example.com']);

            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Updated Name',
                'email' => 'customer1@example.com', // Trying to use customer1's email
            ];

            expect(fn() => $updateCustomer->handle($customer2, $data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('allows updating to a new unique email', function () {
            $customer = Customer::factory()->create(['email' => 'old@example.com']);
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Test User',
                'email' => 'new.unique@example.com',
            ];

            $result = $updateCustomer->handle($customer, $data);

            expect($result->email)->toBe('new.unique@example.com');
        });

        it('succeeds with valid email format', function () {
            $customer = Customer::factory()->create();
            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Test User',
                'email' => 'valid.email+tag@subdomain.example.com',
            ];

            $result = $updateCustomer->handle($customer, $data);

            expect($result->email)->toBe('valid.email+tag@subdomain.example.com');
        });
    });

    describe('Uniqueness Constraint Ignoring Current ID', function () {
        it('ignores current customer id when checking email uniqueness', function () {
            $customer = Customer::factory()->create(['email' => 'myemail@example.com']);
            $customer->refresh();

            $updateCustomer = app(UpdateCustomerAction::class);
            $data = [
                'name' => 'Same Customer Updated',
                'email' => 'myemail@example.com', // Same email
            ];

            $result = $updateCustomer->handle($customer, $data);

            expect($result->name)->toBe('Same Customer Updated');
            expect($result->email)->toBe('myemail@example.com');
            // is_verified should remain unchanged (false by default)
            expect($result->is_verified)->toBeFalse();
        });

        it('preserves timestamps correctly', function () {
            $customer = Customer::factory()->create();
            $originalCreatedAt = $customer->created_at;

            sleep(1); // Ensure time passes

            $updateCustomer = app(UpdateCustomerAction::class);
            $updateCustomer->handle($customer, [
                'name' => 'Updated',
                'email' => 'new@example.com',
            ]);

            $customer->refresh();

            expect($customer->created_at->eq($originalCreatedAt))->toBeTrue();
            expect($customer->updated_at->greaterThan($originalCreatedAt))->toBeTrue();
        });
    });
});
