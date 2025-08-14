<?php

namespace Database\Factories;

use App\Models\Tickets;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketCommentFactory extends Factory
{
    protected $model = \App\Models\TicketComment::class;

    public function definition()
    {
        return [
            'ticket_id' => Tickets::factory(),
            'user_id' => User::factory(),
            'comment' => $this->faker->paragraph(),
            'is_internal' => false,
            'is_solution' => false,
        ];
    }

    public function internal()
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => true,
        ]);
    }

    public function solution()
    {
        return $this->state(fn (array $attributes) => [
            'is_solution' => true,
        ]);
    }
}
