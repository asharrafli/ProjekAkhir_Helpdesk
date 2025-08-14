<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketCategoryFactory extends Factory
{
    protected $model = \App\Models\TicketCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Technical Support', 'Billing', 'General Inquiry', 'Hardware Issue', 'Software Issue', 'Network Problem']),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
