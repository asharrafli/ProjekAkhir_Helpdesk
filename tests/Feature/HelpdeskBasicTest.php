<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tickets;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Models\TicketComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HelpdeskBasicTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $technician;
    protected $customer;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $this->createRolesAndPermissions();
        
        // Create test users with Spatie roles
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password123')
        ]);
        $this->admin->assignRole('admin');

        $this->technician = User::factory()->create([
            'email' => 'tech@test.com',
            'password' => Hash::make('password123')
        ]);
        $this->technician->assignRole('technician');

        $this->customer = User::factory()->create([
            'email' => 'customer@test.com',
            'password' => Hash::make('password123')
        ]);
        $this->customer->assignRole('customer');

        $this->category = TicketCategory::factory()->create();
    }

    private function createRolesAndPermissions()
    {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'technician']);
        Role::create(['name' => 'customer']);

        // Create permissions
        Permission::create(['name' => 'view-tickets']);
        Permission::create(['name' => 'create-tickets']);
        Permission::create(['name' => 'edit-tickets']);
        Permission::create(['name' => 'delete-tickets']);
        Permission::create(['name' => 'assign-tickets']);
        Permission::create(['name' => 'view-all-tickets']);
        Permission::create(['name' => 'view-assigned-tickets']);

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo([
            'view-tickets', 'create-tickets', 'edit-tickets', 
            'delete-tickets', 'assign-tickets', 'view-all-tickets'
        ]);

        $technicianRole = Role::findByName('technician');
        $technicianRole->givePermissionTo([
            'view-tickets', 'edit-tickets', 'view-assigned-tickets'
        ]);

        $customerRole = Role::findByName('customer');
        $customerRole->givePermissionTo(['view-tickets', 'create-tickets']);
    }

    // A. Unit Testing untuk Fitur Autentikasi
    
    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'customer@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($this->customer);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'customer@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function user_can_logout()
    {
        $this->actingAs($this->customer);
        
        $response = $this->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // B. Unit Testing untuk Fitur Customer

    /** @test */
    public function customer_can_create_ticket_with_complete_data()
    {
        $this->actingAs($this->customer);

        $ticketData = [
            'title_ticket' => 'Website Error 500',
            'description_ticket' => 'The website shows error 500 when I try to login',
            'category_id' => $this->category->id,
            'priority' => 'medium',
        ];

        $response = $this->post('/tickets', $ticketData);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'title_ticket' => 'Website Error 500',
            'user_id' => $this->customer->id,
            'status' => 'open'
        ]);
    }

    /** @test */
    public function customer_cannot_create_ticket_without_required_data()
    {
        $this->actingAs($this->customer);

        // Test without title_ticket
        $response = $this->post('/tickets', [
            'description_ticket' => 'Description only',
            'category_id' => $this->category->id,
        ]);

        $response->assertSessionHasErrors(['title_ticket']);

        // Test without description_ticket
        $response = $this->post('/tickets', [
            'title_ticket' => 'Title only',
            'category_id' => $this->category->id,
        ]);

        $response->assertSessionHasErrors(['description_ticket']);
    }

    /** @test */
    public function customer_can_track_their_tickets()
    {
        $this->actingAs($this->customer);

        // Create tickets for this customer
        $ticket1 = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'title_ticket' => 'My First Ticket'
        ]);

        $ticket2 = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'title_ticket' => 'My Second Ticket'
        ]);

        $response = $this->get('/tickets');

        $response->assertStatus(200)
                ->assertSee('My First Ticket')
                ->assertSee('My Second Ticket');
    }

    /** @test */
    public function customer_can_update_their_ticket()
    {
        $this->actingAs($this->customer);

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'title_ticket' => 'Original Title'
        ]);

        $response = $this->patch("/tickets/{$ticket->id}", [
            'title_ticket' => 'Updated Title',
            'description_ticket' => 'Updated Description',
            'category_id' => $this->category->id,
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title_ticket' => 'Updated Title'
        ]);
    }

    // C. Unit Testing untuk Fitur Admin / Super Admin

    /** @test */
    public function admin_can_view_all_tickets()
    {
        $this->actingAs($this->admin);

        // Create tickets from different users
        $customerTicket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'title_ticket' => 'Customer Ticket'
        ]);

        $anotherCustomer = User::factory()->create();
        $anotherCustomer->assignRole('customer');
        $anotherTicket = Tickets::factory()->create([
            'user_id' => $anotherCustomer->id,
            'title_ticket' => 'Another Customer Ticket'
        ]);

        $response = $this->get('/tickets');

        $response->assertStatus(200)
                ->assertSee('Customer Ticket')
                ->assertSee('Another Customer Ticket');
    }

    /** @test */
    public function admin_can_assign_ticket_to_technician()
    {
        $this->actingAs($this->admin);

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'assigned_to' => null
        ]);

        $response = $this->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $this->technician->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $this->technician->id
        ]);
    }

    /** @test */
    public function admin_can_reassign_ticket()
    {
        $this->actingAs($this->admin);

        $anotherTechnician = User::factory()->create();
        $anotherTechnician->assignRole('technician');

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'assigned_to' => $this->technician->id
        ]);

        $response = $this->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $anotherTechnician->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $anotherTechnician->id
        ]);
    }

    /** @test */
    public function admin_can_close_ticket()
    {
        $this->actingAs($this->admin);

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'open'
        ]);

        $response = $this->patch("/tickets/{$ticket->id}", [
            'status' => 'closed',
            'title_ticket' => $ticket->title_ticket,
            'description_ticket' => $ticket->description_ticket,
            'category_id' => $ticket->category_id,
            'priority' => $ticket->priority,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed'
        ]);
    }

    // D. Unit Testing untuk Fitur Teknisi

    /** @test */
    public function technician_can_view_only_assigned_tickets()
    {
        $this->actingAs($this->technician);

        // Create tickets - one assigned to this technician, one not assigned
        $assignedTicket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'assigned_to' => $this->technician->id,
            'title_ticket' => 'Assigned to Me'
        ]);

        $unassignedTicket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'assigned_to' => null,
            'title_ticket' => 'Not Assigned'
        ]);

        $response = $this->get('/tickets/assigned');

        $response->assertStatus(200)
                ->assertSee('Assigned to Me')
                ->assertDontSee('Not Assigned');
    }

    /** @test */
    public function technician_can_update_ticket_status()
    {
        $this->actingAs($this->technician);

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'assigned_to' => $this->technician->id,
            'status' => 'open'
        ]);

        // Update to in progress
        $response = $this->patch("/tickets/{$ticket->id}", [
            'status' => 'in_progress',
            'title_ticket' => $ticket->title_ticket,
            'description_ticket' => $ticket->description_ticket,
            'category_id' => $ticket->category_id,
            'priority' => $ticket->priority,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress'
        ]);

        // Update to resolved
        $response = $this->patch("/tickets/{$ticket->id}", [
            'status' => 'resolved',
            'title_ticket' => $ticket->title_ticket,
            'description_ticket' => $ticket->description_ticket,
            'category_id' => $ticket->category_id,
            'priority' => $ticket->priority,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'resolved'
        ]);
    }

    /** @test */
    public function technician_can_add_notes_to_ticket()
    {
        $this->actingAs($this->technician);

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'assigned_to' => $this->technician->id
        ]);

        $response = $this->post("/tickets/{$ticket->id}/comments", [
            'comment' => 'Investigating the issue. Will update soon.',
            'is_internal' => true
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->technician->id,
            'comment' => 'Investigating the issue. Will update soon.'
        ]);
    }

    // E. Unit Testing untuk Validasi & Keamanan

    /** @test */
    public function customer_cannot_access_admin_pages()
    {
        $this->actingAs($this->customer);

        $response = $this->get('/admin/tickets');
        $response->assertStatus(403);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function customer_can_only_see_their_own_tickets()
    {
        $this->actingAs($this->customer);

        $myTicket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'title_ticket' => 'My Ticket'
        ]);

        $otherCustomer = User::factory()->create();
        $otherCustomer->assignRole('customer');
        $otherTicket = Tickets::factory()->create([
            'user_id' => $otherCustomer->id,
            'title_ticket' => 'Other Customer Ticket'
        ]);

        $response = $this->get('/tickets');

        $response->assertStatus(200)
                ->assertSee('My Ticket')
                ->assertDontSee('Other Customer Ticket');
    }

    /** @test */
    public function customer_cannot_edit_other_users_tickets()
    {
        $this->actingAs($this->customer);

        $otherCustomer = User::factory()->create();
        $otherCustomer->assignRole('customer');
        $otherTicket = Tickets::factory()->create([
            'user_id' => $otherCustomer->id
        ]);

        $response = $this->patch("/tickets/{$otherTicket->id}", [
            'title_ticket' => 'Trying to hack'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function form_validation_works_properly()
    {
        $this->actingAs($this->customer);

        // Test empty fields for ticket creation
        $response = $this->post('/tickets', []);
        $response->assertSessionHasErrors(['title_ticket', 'description_ticket']);

        // Test invalid priority
        $response = $this->post('/tickets', [
            'title_ticket' => 'Test Ticket',
            'description_ticket' => 'Test Description',
            'category_id' => $this->category->id,
            'priority' => 'invalid_priority',
        ]);
        $response->assertSessionHasErrors(['priority']);

        // Test invalid category
        $response = $this->post('/tickets', [
            'title_ticket' => 'Test Ticket',
            'description_ticket' => 'Test Description',
            'category_id' => 99999, // Non-existent category
            'priority' => 'medium',
        ]);
        $response->assertSessionHasErrors(['category_id']);
    }

    /** @test */
    public function ticket_numbers_are_generated_automatically()
    {
        $this->actingAs($this->customer);

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id
        ]);

        $this->assertNotNull($ticket->ticket_number);
        $this->assertStringStartsWith('SLX', $ticket->ticket_number);
    }

    /** @test */
    public function ticket_status_changes_are_tracked()
    {
        $this->actingAs($this->admin);

        $ticket = Tickets::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'open'
        ]);

        $originalLastActivity = $ticket->last_activity_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        $ticket->update(['status' => 'in_progress']);

        $this->assertEquals('in_progress', $ticket->status);
        $this->assertGreaterThan($originalLastActivity, $ticket->fresh()->last_activity_at);
    }

    /** @test */
    public function tickets_can_be_filtered_by_status()
    {
        $this->actingAs($this->admin);

        $openTicket = Tickets::factory()->create([
            'status' => 'open',
            'title_ticket' => 'Open Ticket'
        ]);

        $closedTicket = Tickets::factory()->create([
            'status' => 'closed',
            'title_ticket' => 'Closed Ticket'
        ]);

        $response = $this->get('/tickets?status=open');

        $response->assertStatus(200)
                ->assertSee('Open Ticket')
                ->assertDontSee('Closed Ticket');
    }

    /** @test */
    public function tickets_can_be_searched_by_title()
    {
        $this->actingAs($this->admin);

        $ticket1 = Tickets::factory()->create([
            'title_ticket' => 'Password Reset Issue'
        ]);

        $ticket2 = Tickets::factory()->create([
            'title_ticket' => 'Email Configuration Problem'
        ]);

        $response = $this->get('/tickets?search=Password');

        $response->assertStatus(200)
                ->assertSee('Password Reset Issue')
                ->assertDontSee('Email Configuration Problem');
    }
}
