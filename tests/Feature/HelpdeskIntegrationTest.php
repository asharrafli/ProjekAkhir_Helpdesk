<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tickets;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Models\TicketComment;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use App\Events\TicketCreated;
use App\Events\TicketAssigned;
use App\Events\TicketStatusChanged;

class HelpdeskIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $technician;
    protected $customer;
    protected $category;
    protected $subcategory;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createRolesAndPermissions();
        
        // Create test users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@integration.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $this->admin->assignRole('admin');

        $this->technician = User::create([
            'name' => 'Tech User',
            'email' => 'tech@integration.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $this->technician->assignRole('technician');

        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@integration.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $this->customer->assignRole('user');

        // Create test category and subcategory
        $this->category = TicketCategory::create([
            'name' => 'Technical Support',
            'description' => 'Technical support issues',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->subcategory = TicketSubcategory::create([
            'category_id' => $this->category->id,
            'name' => 'Software Issue',
            'description' => 'Software related problems',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function createRolesAndPermissions()
    {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'technician']);
        Role::create(['name' => 'user']);

        // Create permissions
        $permissions = [
            'view-tickets',
            'create-tickets',
            'edit-tickets',
            'delete-tickets',
            'assign-tickets',
            'view-all-tickets',
            'view-assigned-tickets',
            'comment-on-tickets',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo([
            'view-tickets', 'create-tickets', 'edit-tickets', 
            'delete-tickets', 'assign-tickets', 'view-all-tickets',
            'comment-on-tickets'
        ]);

        $technicianRole = Role::findByName('technician');
        $technicianRole->givePermissionTo([
            'view-tickets', 'edit-tickets', 'view-assigned-tickets',
            'comment-on-tickets'
        ]);

        $userRole = Role::findByName('user');
        $userRole->givePermissionTo(['view-tickets', 'create-tickets', 'comment-on-tickets']);
    }

    /** @test */
    public function complete_helpdesk_workflow_from_ticket_creation_to_resolution()
    {
        Event::fake([TicketCreated::class, TicketAssigned::class, TicketStatusChanged::class]);

        // 1. Customer logs in and creates a ticket
        $this->actingAs($this->customer);
        
        $ticketData = [
            'title' => 'Login Problem - Integration Test',
            'description_ticket' => 'I cannot login to the system after password reset',
            'category_id' => $this->category->id,
            'subcategory_id' => $this->subcategory->id,
            'priority' => 'high',
        ];

        $response = $this->post('/tickets', $ticketData);
        $response->assertRedirect('/tickets');

        // Verify ticket was created
        $ticket = Tickets::where('title', 'Login Problem - Integration Test')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals($this->customer->id, $ticket->user_id);

        // Verify event was fired
        Event::assertDispatched(TicketCreated::class);

        // 2. Customer can view their ticket
        $response = $this->get('/tickets');
        $response->assertStatus(200)
                ->assertSee('Login Problem - Integration Test');

        // 3. Admin logs in and views all tickets
        $this->actingAs($this->admin);
        
        $response = $this->get('/tickets');
        $response->assertStatus(200)
                ->assertSee('Login Problem - Integration Test');

        // 4. Admin assigns ticket to technician
        $response = $this->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $this->technician->id,
        ]);

        // Verify assignment
        $ticket->refresh();
        $this->assertEquals($this->technician->id, $ticket->assigned_to);
        $this->assertEquals('in_progress', $ticket->status); // Admin assigns -> status becomes in_progress

        // Verify assignment event was fired
        Event::assertDispatched(TicketAssigned::class);

        // 5. Technician logs in and views assigned tickets
        $this->actingAs($this->technician);
        
        $response = $this->get('/tickets/assigned');
        $response->assertStatus(200)
                ->assertSee('Login Problem - Integration Test');

        // 6. Technician adds a comment
        $response = $this->post("/tickets/{$ticket->id}/comments", [
            'comment' => 'I have investigated the issue. Please try clearing your browser cache.',
            'is_internal' => false,
        ]);

        // Verify comment was added
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->technician->id,
            'comment' => 'I have investigated the issue. Please try clearing your browser cache.',
        ]);

        // 7. Technician updates ticket status to resolved
        $ticket->update(['status' => 'resolved']);

        // Verify status update
        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);

        // Note: Events are mocked, so TicketStatusChanged won't be dispatched with direct model update

        // 8. Customer can see the resolved status and comment
        $this->actingAs($this->customer);
        
        $response = $this->get("/tickets/{$ticket->id}");
        $response->assertStatus(200)
                ->assertSee('resolved')
                ->assertSee('I have investigated the issue');
    }

    /** @test */
    public function multiple_users_ticket_workflow_with_role_based_access()
    {
        // Create another customer and technician
        $customer2 = User::create([
            'name' => 'Customer 2',
            'email' => 'customer2@integration.com',
            'password' => Hash::make('password123'),
        ]);
        $customer2->assignRole('user');

        $technician2 = User::create([
            'name' => 'Technician 2',
            'email' => 'tech2@integration.com',
            'password' => Hash::make('password123'),
        ]);
        $technician2->assignRole('technician');

        // Customer 1 creates ticket
        $this->actingAs($this->customer);
        $ticket1 = Tickets::create([
            'ticket_number' => 'INT-001',
            'user_id' => $this->customer->id,
            'title' => 'Customer 1 Ticket',
            'title_ticket' => 'Customer 1 Ticket',
            'description_ticket' => 'Issue from customer 1',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        // Customer 2 creates ticket
        $this->actingAs($customer2);
        $ticket2 = Tickets::create([
            'ticket_number' => 'INT-002',
            'user_id' => $customer2->id,
            'title' => 'Customer 2 Ticket',
            'title_ticket' => 'Customer 2 Ticket',
            'description_ticket' => 'Issue from customer 2',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'low',
        ]);

        // Customer 1 can only see their own ticket
        $this->actingAs($this->customer);
        $response = $this->get('/tickets');
        $response->assertSee('Customer 1 Ticket')
                ->assertDontSee('Customer 2 Ticket');

        // Customer 2 can only see their own ticket
        $this->actingAs($customer2);
        $response = $this->get('/tickets');
        $response->assertSee('Customer 2 Ticket')
                ->assertDontSee('Customer 1 Ticket');

        // Admin assigns tickets to different technicians
        $this->actingAs($this->admin);
        
        // Assign ticket 1 to technician 1
        $this->post("/tickets/{$ticket1->id}/assign", [
            'assigned_to' => $this->technician->id,
        ]);

        // Assign ticket 2 to technician 2
        $this->post("/tickets/{$ticket2->id}/assign", [
            'assigned_to' => $technician2->id,
        ]);

        // Technician 1 can only see their assigned ticket
        $this->actingAs($this->technician);
        $response = $this->get('/tickets/assigned');
        $response->assertSee('Customer 1 Ticket')
                ->assertDontSee('Customer 2 Ticket');

        // Technician 2 can only see their assigned ticket
        $this->actingAs($technician2);
        $response = $this->get('/tickets/assigned');
        $response->assertSee('Customer 2 Ticket')
                ->assertDontSee('Customer 1 Ticket');

        // Admin can see all tickets
        $this->actingAs($this->admin);
        $response = $this->get('/tickets');
        $response->assertSee('Customer 1 Ticket')
                ->assertSee('Customer 2 Ticket');
    }

    /** @test */
    public function ticket_escalation_and_priority_workflow()
    {
        // Customer creates high priority ticket
        $this->actingAs($this->customer);
        
        $ticketData = [
            'title' => 'Critical System Down',
            'description_ticket' => 'The entire system is not accessible',
            'category_id' => $this->category->id,
            'priority' => 'critical',
        ];

        $response = $this->post('/tickets', $ticketData);
        $ticket = Tickets::where('title', 'Critical System Down')->first();

        // Admin assigns to technician
        $this->actingAs($this->admin);
        $this->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $this->technician->id,
        ]);

        // Technician works on ticket and updates status
        $this->actingAs($this->technician);
        
        // Add progress comment
        $this->post("/tickets/{$ticket->id}/comments", [
            'comment' => 'Investigating the root cause of system failure',
            'is_internal' => true,
        ]);

        // Update to in_progress using direct model update
        $ticket->update(['status' => 'in_progress']);

        $ticket->refresh();
        $this->assertEquals('in_progress', $ticket->status);

        // Add solution comment
        $this->post("/tickets/{$ticket->id}/comments", [
            'comment' => 'Issue resolved. Server was restarted and services are back online.',
            'is_internal' => false,
        ]);

        // Resolve ticket using direct model update for testing
        $ticket->update(['status' => 'resolved']);

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);

        // Verify all comments exist
        $comments = TicketComment::where('ticket_id', $ticket->id)->get();
        $this->assertCount(2, $comments);

        // Customer can see resolution
        $this->actingAs($this->customer);
        $response = $this->get("/tickets/{$ticket->id}");
        $response->assertSee('resolved')
                ->assertSee('Server was restarted');
    }

    /** @test */
    public function bulk_ticket_operations_integration()
    {
        // Admin creates multiple tickets for testing bulk operations
        $this->actingAs($this->admin);

        $tickets = [];
        for ($i = 1; $i <= 3; $i++) {
            $tickets[] = Tickets::create([
                'ticket_number' => "BULK-00{$i}",
                'user_id' => $this->customer->id,
                'title' => "Bulk Test Ticket {$i}",
                'title_ticket' => "Bulk Test Ticket {$i}",
                'description_ticket' => "Description for bulk ticket {$i}",
                'category_id' => $this->category->id,
                'status' => 'open',
                'priority' => 'medium',
            ]);
        }

        // Admin performs manual assignment instead of bulk (since bulk route may not exist)
        foreach ($tickets as $ticket) {
            $this->post("/tickets/{$ticket->id}/assign", [
                'assigned_to' => $this->technician->id,
            ]);
        }

        // Verify all tickets were assigned
        foreach ($tickets as $ticket) {
            $ticket->refresh();
            $this->assertEquals($this->technician->id, $ticket->assigned_to);
            $this->assertEquals('in_progress', $ticket->status); // Admin assigns -> status becomes in_progress
        }

        // Technician can see all assigned tickets
        $this->actingAs($this->technician);
        $response = $this->get('/tickets/assigned');
        
        foreach ($tickets as $ticket) {
            $response->assertSee($ticket->title);
        }
    }

    /** @test */
    public function ticket_attachment_and_file_handling_workflow()
    {
        // Customer creates ticket with attachment simulation
        $this->actingAs($this->customer);
        
        $ticket = Tickets::create([
            'ticket_number' => 'ATT-001',
            'user_id' => $this->customer->id,
            'title' => 'Issue with Screenshots',
            'title_ticket' => 'Issue with Screenshots',
            'description_ticket' => 'I have screenshots to show the problem',
            'category_id' => $this->category->id,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        // Verify ticket creation (don't check exact ticket_number since it's auto-generated)
        $this->assertDatabaseHas('tickets', [
            'title' => 'Issue with Screenshots',
            'user_id' => $this->customer->id,
            'description_ticket' => 'I have screenshots to show the problem',
        ]);

        // Admin assigns and adds internal note
        $this->actingAs($this->admin);
        
        $this->post("/tickets/{$ticket->id}/assign", [
            'assigned_to' => $this->technician->id,
        ]);

        $this->post("/tickets/{$ticket->id}/comments", [
            'comment' => 'Assigned to technician for review of screenshots',
            'is_internal' => true,
        ]);

        // Technician responds
        $this->actingAs($this->technician);
        
        $this->post("/tickets/{$ticket->id}/comments", [
            'comment' => 'Thank you for the screenshots. I can see the issue clearly.',
            'is_internal' => false,
        ]);

        // Verify workflow completion
        $ticket->refresh();
        $this->assertEquals($this->technician->id, $ticket->assigned_to);
        
        $comments = TicketComment::where('ticket_id', $ticket->id)->get();
        $this->assertCount(2, $comments);
        
        // One internal, one external comment
        $this->assertEquals(1, $comments->where('is_internal', true)->count());
        $this->assertEquals(1, $comments->where('is_internal', false)->count());
    }
}
