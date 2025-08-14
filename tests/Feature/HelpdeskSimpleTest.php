<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tickets;
use App\Models\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HelpdeskSimpleTest extends TestCase
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
        
        // Create test users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $this->admin->assignRole('admin');

        $this->technician = User::create([
            'name' => 'Tech User',
            'email' => 'tech@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $this->technician->assignRole('technician');

        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $this->customer->assignRole('user'); // Use 'user' instead of 'customer'

        // Create test category
        $this->category = TicketCategory::create([
            'name' => 'Technical Support',
            'description' => 'Technical support issues',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function createRolesAndPermissions()
    {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'technician']);
        Role::create(['name' => 'user']); // Use 'user' to match seeder

        // Create permissions
        Permission::create(['name' => 'view-tickets']);
        Permission::create(['name' => 'create-tickets']);
        Permission::create(['name' => 'edit-tickets']);
        Permission::create(['name' => 'delete-tickets']);
        Permission::create(['name' => 'assign-tickets']);
        Permission::create(['name' => 'view-all-tickets']);
        Permission::create(['name' => 'view-assigned-tickets']); // Add missing permission

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

        $userRole = Role::findByName('user');
        $userRole->givePermissionTo(['view-tickets', 'create-tickets']);
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
            'title' => 'Website Error 500',
            'description_ticket' => 'The website shows error 500 when I try to login',
            'category_id' => $this->category->id,
            'priority' => 'medium',
        ];

        $response = $this->post('/tickets', $ticketData);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'title' => 'Website Error 500',
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

        $response->assertSessionHasErrors(['title']);
    }

    /** @test */
    public function customer_can_track_their_tickets()
    {
        $this->actingAs($this->customer);

        // Create tickets for this customer
        $ticket1 = Tickets::create([
            'ticket_number' => 'TEST-001',
            'user_id' => $this->customer->id,
            'title' => 'My First Ticket',
            'title_ticket' => 'My First Ticket',
            'description_ticket' => 'First ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $ticket2 = Tickets::create([
            'ticket_number' => 'TEST-002',
            'user_id' => $this->customer->id,
            'title' => 'My Second Ticket',
            'title_ticket' => 'My Second Ticket',
            'description_ticket' => 'Second ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'low',
        ]);

        $response = $this->get('/tickets');

        $response->assertStatus(200)
                ->assertSee('My First Ticket')
                ->assertSee('My Second Ticket');
    }

    // C. Unit Testing untuk Fitur Admin

    /** @test */
    public function admin_can_view_all_tickets()
    {
        $this->actingAs($this->admin);

        // Create tickets from different users
        $customerTicket = Tickets::create([
            'ticket_number' => 'ADMIN-001',
            'user_id' => $this->customer->id,
            'title' => 'Customer Ticket',
            'title_ticket' => 'Customer Ticket',
            'description_ticket' => 'Customer ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $anotherCustomer = User::create([
            'name' => 'Another Customer',
            'email' => 'another@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $anotherCustomer->assignRole('user');

        $anotherTicket = Tickets::create([
            'ticket_number' => 'ADMIN-002',
            'user_id' => $anotherCustomer->id,
            'title' => 'Another Customer Ticket',
            'title_ticket' => 'Another Customer Ticket',
            'description_ticket' => 'Another ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'high',
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

        $ticket = Tickets::create([
            'ticket_number' => 'ASSIGN-001',
            'user_id' => $this->customer->id,
            'title' => 'Ticket to Assign',
            'title_ticket' => 'Ticket to Assign',
            'description_ticket' => 'Ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => null,
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

    // D. Unit Testing untuk Fitur Teknisi

    /** @test */
    public function technician_can_view_only_assigned_tickets()
    {
        $this->actingAs($this->technician);

        // Create tickets - one assigned to this technician, one not assigned
        $assignedTicket = Tickets::create([
            'ticket_number' => 'TECH-001',
            'user_id' => $this->customer->id,
            'assigned_to' => $this->technician->id,
            'title' => 'Assigned to Me',
            'title_ticket' => 'Assigned to Me',
            'description_ticket' => 'Assigned ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $unassignedTicket = Tickets::create([
            'ticket_number' => 'TECH-002',
            'user_id' => $this->customer->id,
            'assigned_to' => null,
            'title' => 'Not Assigned',
            'title_ticket' => 'Not Assigned',
            'description_ticket' => 'Unassigned ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->get('/tickets/assigned');

        $response->assertStatus(200)
                ->assertSee('Assigned to Me')
                ->assertDontSee('Not Assigned');
    }

    // E. Unit Testing untuk Validasi & Keamanan

    /** @test */
    public function customer_can_only_see_their_own_tickets()
    {
        $this->actingAs($this->customer);

        $myTicket = Tickets::create([
            'ticket_number' => 'SEC-001',
            'user_id' => $this->customer->id,
            'title' => 'My Ticket',
            'title_ticket' => 'My Ticket',
            'description_ticket' => 'My ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $otherCustomer->assignRole('user');

        $otherTicket = Tickets::create([
            'ticket_number' => 'SEC-002',
            'user_id' => $otherCustomer->id,
            'title' => 'Other Customer Ticket',
            'title_ticket' => 'Other Customer Ticket',
            'description_ticket' => 'Other ticket description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->get('/tickets');

        $response->assertStatus(200)
                ->assertSee('My Ticket')
                ->assertDontSee('Other Customer Ticket');
    }

    /** @test */
    public function form_validation_works_properly()
    {
        $this->actingAs($this->customer);

        // Test empty fields for ticket creation
        $response = $this->post('/tickets', []);
        $response->assertSessionHasErrors(['title', 'description_ticket']);

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

        $ticket = Tickets::create([
            'user_id' => $this->customer->id,
            'title' => 'Auto Number Test',
            'title_ticket' => 'Auto Number Test',
            'description_ticket' => 'Test description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $this->assertNotNull($ticket->ticket_number);
        $this->assertStringStartsWith('SLX', $ticket->ticket_number);
    }

    /** @test */
    public function ticket_status_changes_are_tracked()
    {
        $this->actingAs($this->admin);

        $ticket = Tickets::create([
            'ticket_number' => 'STATUS-001',
            'user_id' => $this->customer->id,
            'title' => 'Status Test',
            'title_ticket' => 'Status Test',
            'description_ticket' => 'Test description',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $originalLastActivity = $ticket->last_activity_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        $ticket->update(['status' => 'in_progress']);

        $this->assertEquals('in_progress', $ticket->status);
        $this->assertGreaterThan($originalLastActivity, $ticket->fresh()->last_activity_at);
    }
}
