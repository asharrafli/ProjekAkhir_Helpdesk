<?php

use App\Models\User;
use App\Models\Tickets;
use App\Models\TicketCategory;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'super-admin']);
    Role::create(['name' => 'technician']);
    Role::create(['name' => 'user']);
});

it('automatically sets status to in_progress when admin assigns ticket', function () {
    // Create users
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $technician = User::factory()->create();
    $technician->assignRole('technician');
    
    $customer = User::factory()->create();
    $customer->assignRole('user');

    // Create category
    $category = TicketCategory::factory()->create();

    // Create ticket
    $ticket = Tickets::factory()->create([
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'status' => 'open',
        'assigned_to' => null,
    ]);

    // Act as admin and assign ticket
    $response = $this->actingAs($admin)
        ->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $technician->id,
            'assignment_notes' => 'Assigning to technician'
        ]);

    // Refresh ticket from database
    $ticket->refresh();

    // Assert ticket status is automatically set to 'in_progress'
    expect($ticket->status)->toBe('in_progress');
    expect($ticket->assigned_to)->toBe($technician->id);
    expect($ticket->first_response_at)->not->toBeNull();
});

it('automatically sets status to in_progress when super-admin assigns ticket', function () {
    // Create users
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    
    $technician = User::factory()->create();
    $technician->assignRole('technician');
    
    $customer = User::factory()->create();
    $customer->assignRole('user');

    // Create category
    $category = TicketCategory::factory()->create();

    // Create ticket
    $ticket = Tickets::factory()->create([
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'status' => 'open',
        'assigned_to' => null,
    ]);

    // Act as super-admin and assign ticket
    $response = $this->actingAs($superAdmin)
        ->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $technician->id,
        ]);

    // Refresh ticket from database
    $ticket->refresh();

    // Assert ticket status is automatically set to 'in_progress'
    expect($ticket->status)->toBe('in_progress');
    expect($ticket->assigned_to)->toBe($technician->id);
    expect($ticket->first_response_at)->not->toBeNull();
});

it('prevents assignment to users without technician roles', function () {
    // Create users
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $regularUser = User::factory()->create();
    $regularUser->assignRole('user'); // Not a technician
    
    $customer = User::factory()->create();
    $customer->assignRole('user');

    // Create category
    $category = TicketCategory::factory()->create();

    // Create ticket
    $ticket = Tickets::factory()->create([
        'user_id' => $customer->id,
        'category_id' => $category->id,
        'status' => 'open',
        'assigned_to' => null,
    ]);

    // Act as admin and try to assign ticket to regular user
    $response = $this->actingAs($admin)
        ->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $regularUser->id,
        ]);

    // Should redirect back with error
    $response->assertRedirect();
    $response->assertSessionHas('error', 'Cannot assign tickets to users without technician privileges.');

    // Ticket should remain unchanged
    $ticket->refresh();
    expect($ticket->status)->toBe('open');
    expect($ticket->assigned_to)->toBeNull();
});

it('prevents assignment of closed or resolved tickets', function () {
    // Create users
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $technician = User::factory()->create();
    $technician->assignRole('technician');

    // Create category
    $category = TicketCategory::factory()->create();

    // Create closed ticket
    $ticket = Tickets::factory()->create([
        'category_id' => $category->id,
        'status' => 'closed',
    ]);

    // Act as admin and try to assign closed ticket
    $response = $this->actingAs($admin)
        ->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $technician->id,
        ]);

    // Should redirect back with error
    $response->assertRedirect();
    $response->assertSessionHas('error', 'Cannot assign tickets that are already closed or resolved.');

    // Ticket should remain unchanged
    $ticket->refresh();
    expect($ticket->status)->toBe('closed');
});

it('handles bulk assignment with auto-status update', function () {
    // Create users
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $technician = User::factory()->create();
    $technician->assignRole('technician');

    // Create category
    $category = TicketCategory::factory()->create();

    // Create multiple tickets
    $tickets = Tickets::factory()->count(3)->create([
        'category_id' => $category->id,
        'status' => 'open',
        'assigned_to' => null,
    ]);

    $ticketIds = $tickets->pluck('id')->toArray();

    // Act as admin and bulk assign tickets
    $response = $this->actingAs($admin)
        ->post('/tickets/bulk-update', [
            'ticket_ids' => $ticketIds,
            'action' => 'assign',
            'assigned_to' => $technician->id,
        ]);

    // Should return successful JSON response
    $response->assertJson(['success' => true]);

    // All tickets should be assigned with 'in_progress' status
    $tickets->each(function ($ticket) use ($technician) {
        $ticket->refresh();
        expect($ticket->status)->toBe('in_progress');
        expect($ticket->assigned_to)->toBe($technician->id);
        expect($ticket->first_response_at)->not->toBeNull();
    });
});