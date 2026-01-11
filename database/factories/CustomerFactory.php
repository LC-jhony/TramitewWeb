<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isNaturalPerson = fake()->boolean(70); // 70% persona natural, 30% jurídica

        if ($isNaturalPerson) {
            // Persona Natural
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();

            return [
                'representation' => false, // Persona Natural
                'full_name' => $firstName . ' ' . $lastName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'dni' => fake()->numerify('########'), // 8 dígitos
                'phone' => fake()->numerify('9########'), // Celular peruano
                'email' => fake()->unique()->safeEmail(),
                'address' => fake()->address(),
                'ruc' => null,
                'company' => null,
            ];
        } else {
            // Persona Jurídica
            $company = fake()->company();

            return [
                'representation' => true, // Persona Jurídica
                'full_name' => $company,
                'first_name' => null,
                'last_name' => null,
                'dni' => null,
                'phone' => fake()->numerify('01#######'), // Teléfono fijo
                'email' => fake()->unique()->companyEmail(),
                'address' => fake()->address(),
                'ruc' => fake()->numerify('20#########'), // RUC empieza con 20
                'company' => $company,
            ];
        }
    }

    /**
     * Persona Natural state.
     */
    public function natural(): static
    {
        return $this->state(function (array $attributes) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();

            return [
                'representation' => false,
                'full_name' => $firstName . ' ' . $lastName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'dni' => fake()->numerify('########'),
                'phone' => fake()->numerify('9########'),
                'ruc' => null,
                'company' => null,
            ];
        });
    }

    /**
     * Persona Jurídica state.
     */
    public function juridica(): static
    {
        return $this->state(function (array $attributes) {
            $company = fake()->company();

            return [
                'representation' => true,
                'full_name' => $company,
                'first_name' => null,
                'last_name' => null,
                'dni' => null,
                'phone' => fake()->numerify('01#######'),
                'ruc' => fake()->numerify('20#########'),
                'company' => $company,
            ];
        });
    }
}
