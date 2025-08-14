<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketsFactory extends Factory
{
    protected $model = \App\Models\Tickets::class;

    public function definition()
    {
        return [
            'ticket_number' => 'SLX' . date('Ymd') . '-' . str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'assigned_to' => null,
            'category_id' => TicketCategory::factory(),
            'subcategory_id' => null,
            'title' => $this->faker->sentence(),
            'title_ticket' => $this->faker->sentence(),
            'description_ticket' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'assigned', 'pending', 'resolved', 'closed']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical', 'urgent']),
            'resolved_at' => null,
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'attachments' => null,
            'first_response_at' => null,
            'last_activity_at' => now(),
            'response_time_minutes' => null,
            'resolution_notes' => null,
            'is_escalated' => false,
            'escalated_at' => null,
            'sla_data' => null,
        ];
    }

    public function open()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'assigned_to' => null,
        ]);
    }

    public function assigned()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'assigned',
            'assigned_to' => User::factory(),
        ]);
    }

    public function resolved()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }
}
