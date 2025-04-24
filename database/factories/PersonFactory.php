<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group' => $this->faker->randomElement(['Public', 'Beneficiaries', 'Co-Payment']),
            'phone' => $this->faker->phoneNumber,
            'is_admin' => $this->faker->boolean,
            'credit' => $this->faker->randomFloat(2, 0, 10000),
            'cash' => $this->faker->randomFloat(2, 0, 10000),
            'credit_portion' => $this->faker->randomFloat(2, 0, 1),
            'cash_portion' => $this->faker->randomFloat(2, 0, 1),
            'is_active' => $this->faker->boolean,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
