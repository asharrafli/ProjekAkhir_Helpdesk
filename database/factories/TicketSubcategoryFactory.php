<?php

namespace Database\Factories;

use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketSubcategoryFactory extends Factory
{
    protected $model = \App\Models\TicketSubcategory::class;

    public function definition()
    {
        return [
            'category_id' => TicketCategory::factory(),
            'name' => $this->faker->randomElement(['Login Issues', 'Email Problems', 'Server Down', 'Application Error', 'Database Issue']),
            'slug' => $this->faker->slug(),
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
