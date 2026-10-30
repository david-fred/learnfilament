<?php

use App\Actions\CreateCustomerAction;
use App\Models\Customer;

describe('CreateCustomerAction', function () {
    describe('Valid Creation', function () {
        it('creates a customer with valid attributes', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ];

            $customer = $createCustomer->handle($data);
            $customer->refresh(); // Refresh to get database defaults

            expect($customer)->toBeInstanceOf(Customer::class);
            expect($customer->id)->not->toBeNull();
            expect($customer->name)->toBe('John Doe');
            expect($customer->email)->toBe('john@example.com');
            expect($customer->is_verified)->toBeFalse();
        });

        it('persists customer to database', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
            ];

            $customer = $createCustomer->handle($data);

            $this->assertDatabaseHas('customers', [
                'id' => $customer->id,
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'is_verified' => false,
            ]);
        });

        it('returns model instance', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
            ];

            $result = $createCustomer->handle($data);

            expect($result)->toBeInstanceOf(Customer::class);
            expect($result->id)->not->toBeNull();
        });
    });

    describe('Name Validation', function () {
        it('fails when name is missing', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'email' => 'test@example.com',
            ];

            expect(fn () => $createCustomer->handle($data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when name is not a string', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 12345,
                'email' => 'test@example.com',
            ];

            expect(fn () => $createCustomer->handle($data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when name exceeds 150 characters', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => str_repeat('a', 151),
                'email' => 'test@example.com',
            ];

            expect(fn () => $createCustomer->handle($data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('succeeds with name at max length of 150 characters', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $name = str_repeat('a', 150);
            $data = [
                'name' => $name,
                'email' => 'test@example.com',
            ];

            $customer = $createCustomer->handle($data);

            expect($customer->name)->toBe($name);
            expect(strlen($customer->name))->toBe(150);
        });
    });

    describe('Email Validation', function () {
        it('fails when email is missing', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'Test User',
            ];

            expect(fn () => $createCustomer->handle($data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when email is not a string', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'Test User',
                'email' => 12345,
            ];

            expect(fn () => $createCustomer->handle($data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails with invalid email format', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'Test User',
                'email' => 'invalid-email',
            ];

            expect(fn () => $createCustomer->handle($data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when email already exists', function () {
            Customer::factory()->create(['email' => 'existing@example.com']);

            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'New User',
                'email' => 'existing@example.com',
            ];

            expect(fn () => $createCustomer->handle($data))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('succeeds with valid email format', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $data = [
                'name' => 'Test User',
                'email' => 'valid.email+tag@subdomain.example.com',
            ];

            $customer = $createCustomer->handle($data);

            expect($customer->email)->toBe('valid.email+tag@subdomain.example.com');
        });
    });

    describe('Transaction Atomicity', function () {
        it('rolls back on validation failure', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $initialCount = Customer::count();

            try {
                $createCustomer->handle([
                    'name' => 'Invalid',
                    // Missing email
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Expected exception
            }

            expect(Customer::count())->toBe($initialCount);
        });

        it('rolls back on any exception during creation', function () {
            $createCustomer = app(CreateCustomerAction::class);
            $initialCount = Customer::count();

            try {
                $createCustomer->handle([
                    'name' => 'Test',
                    'email' => 'duplicate@example.com',
                ]);
                $createCustomer->handle([
                    'name' => 'Test Again',
                    'email' => 'duplicate@example.com',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Expected exception
            }

            // Should still be initialCount + 1 (only first succeeded)
            expect(Customer::count())->toBe($initialCount + 1);
        });
    });
});
