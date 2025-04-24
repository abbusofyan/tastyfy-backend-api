<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'=> User::factory(),
            'customer_id' => $this->faker->unique()->numerify('CUST####'),
            'credit_balance' => $this->faker->randomFloat(2, 0, 10000),
            'cash_balance' => $this->faker->randomFloat(2, 0, 10000),
            'credit_split' => 50,
            'cash_split' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Customer $customer) {
            $role = CustomerRole::inRandomOrder()->first();;
            $customer->assignRole($role);
        });
    }
}
