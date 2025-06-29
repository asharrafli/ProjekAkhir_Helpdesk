<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tickets;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Events\TicketCreated;
use App\Events\TicketAssigned;
use App\Events\TicketStatusChanged;
use App\Notifications\TicketCreated as TicketCreatedNotification;
use App\Notifications\TicketAssigned as TicketAssignedNotification;
use App\Notifications\TicketStatusChanged as TicketStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view-tickets'])->only(['index', 'show']);
        $this->middleware(['permission:create-tickets'])->only(['create', 'store']);
        $this->middleware(['permission:edit-tickets'])->only(['edit', 'update']);
        $this->middleware(['permission:delete-tickets'])->only(['destroy']);
        $this->middleware(['permission:assign-tickets'])->only(['assign', 'claim']);
        $this->middleware(['permission:bulk-ticket-operations'])->only(['bulkUpdate']);
    }

    public function index(Request $request)
    {
        $query = Tickets::with(['user', 'assignedTo', 'category', 'subcategory']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
                  ->orWhere('description_ticket', 'LIKE', "%{$search}%");
            });
        }
        
        // Role-based filtering
        $currentUser = $this->getCurrentUser();
        if ($currentUser && !$currentUser->can('view-all-tickets')) {
            if ($currentUser->hasRole('technician')) {
                $query->where('assigned_to', $currentUser->id);
            } else {
                $query->where('user_id', $this->getCurrentUserId());
            }
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $categories = TicketCategory::all();
        $technicians = User::role('technician')->get();
        
        return view('tickets.index', compact('tickets', 'categories', 'technicians'));
    }

    public function create()
    {
        $categories = TicketCategory::all();
        $subcategories = TicketSubcategory::all();
        $technicians = User::role('technician')->get();
        
        return view('tickets.create', compact('categories', 'subcategories', 'technicians'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tickets::class);

        $request->validate([
            'category_id' => 'required|exists:ticket_categories,id',
            'subcategory_id' => 'nullable|exists:ticket_subcategories,id',
            'title' => 'required|string|max:255',
            'description_ticket' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip|max:10240',
        ]);

        $ticket = Tickets::create([
            'ticket_number' => 'TKT-' . time(),
            'user_id' => $this->getCurrentUserId(),
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'title' => $request->title,
            'title_ticket' => $request->title, // Copy value from title to title_ticket
            'description_ticket' => $request->description_ticket,
            'priority' => $request->priority,
            'assigned_to' => $request->assigned_to,
            'status' => $request->assigned_to ? 'assigned' : 'open',
            'last_activity_at' => now(),
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($ticket, $file);
            }
        }

        // Send notifications
        $this->sendTicketCreatedNotifications($ticket);

        // Broadcast event
        event(new \App\Events\TicketCreated($ticket));

        // If assigned, send assignment notification
        if ($request->assigned_to) {
            $this->sendTicketAssignedNotifications($ticket);
            event(new \App\Events\TicketAssigned($ticket));
        }

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket created successfully!');
    }

    public function show(Tickets $ticket)
    {
        $this->authorize('view', $ticket);
        
        $ticket->load(['user', 'assignedTo', 'category', 'subcategory', 'attachments', 'activities']);
        $technicians = User::role('technician')->get();
        
        return view('tickets.show', compact('ticket', 'technicians'));
    }

    public function edit(Tickets $ticket)
    {
        $this->authorize('update', $ticket);
        
        $categories = TicketCategory::all();
        $subcategories = TicketSubcategory::all();
        $technicians = User::role('technician')->get();
        
        return view('tickets.edit', compact('ticket', 'categories', 'subcategories', 'technicians'));
    }

    public function update(Request $request, Tickets $ticket)
    {
        $this->authorize('update', $ticket);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description_ticket' => 'required|string',
            'category_id' => 'required|exists:ticket_categories,id',
            'subcategory_id' => 'nullable|exists:ticket_subcategories,id',
            'priority' => 'required|in:low,medium,high,critical,urgent',
            'status' => 'required|in:open,in_progress,assigned,pending,escalated,closed,resolved',
            'assigned_to' => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip|max:10240',
        ]);

        $oldStatus = $ticket->status;
        $oldAssignedTo = $ticket->assigned_to;

        $ticket->update([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'description_ticket' => $request->description_ticket,
            'priority' => $request->priority,
            'status' => $request->status,
            'assigned_to' => $request->assigned_to,
            'resolution_notes' => $request->resolution_notes,
            'last_activity_at' => now(),
        ]);

        // Handle new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($ticket, $file);
            }
        }

        // Send notifications for status changes
        if ($oldStatus !== $ticket->status) {
            event(new TicketStatusChanged($ticket, $oldStatus, $ticket->status));
        }

        // Send notifications for assignment changes
        if ($oldAssignedTo !== $ticket->assigned_to) {
            event(new TicketAssigned($ticket));
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully!');
    }

    /**
     * Update ticket status only
     */
    public function updateStatus(Request $request, Tickets $ticket)
    {
        $this->authorize('update', $ticket);
        
        $request->validate([
            'status' => 'required|in:open,in_progress,assigned,pending,escalated,closed,resolved',
            'resolution_notes' => 'required_if:status,resolved,closed',
        ]);

        $oldStatus = $ticket->status;
        
        // Log received data for debugging
        Log::info('Ticket status update request:', [
            'ticket_id' => $ticket->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'has_resolution_notes' => $request->has('resolution_notes'),
            'resolution_notes_length' => $request->has('resolution_notes') ? strlen($request->resolution_notes) : 0
        ]);
        
        // If resolving or closing, make sure we have resolution notes
        if (in_array($request->status, ['resolved', 'closed']) && empty($request->resolution_notes)) {
            return redirect()->back()
                ->with('error', 'Resolution notes are required when marking a ticket as resolved or closed.')
                ->withInput();
        }

        $updateData = [
            'status' => $request->status,
            'last_activity_at' => now(),
        ];
        
        // Add resolution notes if provided
        if ($request->filled('resolution_notes')) {
            $updateData['resolution_notes'] = $request->resolution_notes;
        }
        
        // Add resolved_at timestamp if ticket is being resolved
        if ($request->status === 'resolved' && $oldStatus !== 'resolved') {
            $updateData['resolved_at'] = now();
        }
        
        try {
            $ticket->update($updateData);
            
            // Log successful update
            Log::info('Ticket status updated successfully', [
                'ticket_id' => $ticket->id,
                'old_status' => $oldStatus,
                'new_status' => $ticket->status
            ]);

            // Send notifications for status changes
            if ($oldStatus !== $ticket->status) {
                event(new \App\Events\TicketStatusChanged($ticket, $oldStatus, $ticket->status));
            }

            return redirect()->route('tickets.show', $ticket)
                ->with('success', 'Ticket status updated successfully!');
        } catch (\Exception $e) {
            // Log error
            Log::error('Error updating ticket status', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error updating ticket status: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Tickets $ticket)
    {
        $this->authorize('delete', $ticket);
        
        $ticket->delete();
        
        return redirect()->route('tickets.index')
            ->with('success', 'Ticket deleted successfully!');
    }

    public function claim(Tickets $ticket)
    {
        $this->authorize('claim', $ticket);
        
        $ticket->update([
            'assigned_to' => $this->getCurrentUserId(),
            'status' => 'in_progress',
            'first_response_at' => $ticket->first_response_at ?: now(),
            'last_activity_at' => now(),
        ]);

        event(new TicketAssigned($ticket));

        return redirect()->back()
            ->with('success', 'Ticket claimed successfully!');
    }

    public function assign(Request $request, Tickets $ticket)
    {
        $this->authorize('assign', $ticket);
        
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'assignment_notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Check if ticket is already closed or resolved
            if (in_array($ticket->status, ['closed', 'resolved'])) {
                return redirect()->back()
                    ->with('error', 'Cannot assign tickets that are already closed or resolved.');
            }

            // Get the user being assigned to validate they can receive assignments
            $assignedUser = User::find($request->assigned_to);
            if (!$assignedUser->hasAnyRole(['technician', 'admin', 'super-admin'])) {
                return redirect()->back()
                    ->with('error', 'Cannot assign tickets to users without technician privileges.');
            }

            // Determine status based on who is assigning
            $assignerUser = $this->getCurrentUser();
            $newStatus = 'assigned'; // Default status
            
            // If admin or super-admin is assigning, automatically set to 'in_progress'
            if ($assignerUser->hasAnyRole(['admin', 'super-admin']) || $assignerUser->can('assign-tickets')) {
                $newStatus = 'in_progress';
            }

            // Store old values for logging
            $oldStatus = $ticket->status;
            $oldAssignedTo = $ticket->assigned_to;

            $ticket->update([
                'assigned_to' => $request->assigned_to,
                'status' => $newStatus,
                'last_activity_at' => now(),
                'first_response_at' => $newStatus === 'in_progress' && !$ticket->first_response_at ? now() : $ticket->first_response_at,
            ]);

            // Log the assignment with status change details
            activity()
                ->performedOn($ticket)
                ->withProperties([
                    'assigned_to' => $request->assigned_to,
                    'old_assigned_to' => $oldAssignedTo,
                    'status_changed_from' => $oldStatus,
                    'status_changed_to' => $newStatus,
                    'assignment_notes' => $request->assignment_notes,
                    'assigned_by' => $assignerUser->id,
                    'assigned_by_role' => $assignerUser->getRoleNames()->first(),
                ])
                ->log('Ticket assigned with auto-status update');

            event(new TicketAssigned($ticket));

            $statusMessage = $newStatus === 'in_progress' 
                ? 'Ticket assigned successfully and status automatically set to in progress!' 
                : 'Ticket assigned successfully!';

            return redirect()->back()
                ->with('success', $statusMessage);

        } catch (\Exception $e) {
            Log::error('Ticket assignment failed', [
                'ticket_id' => $ticket->id,
                'assigned_to' => $request->assigned_to,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign ticket. Please try again.');
        }
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'action' => 'required|in:assign,status,priority,delete',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
            'status' => 'required_if:action,status|in:open,in_progress,assigned,pending,escalated,closed,resolved',
            'priority' => 'required_if:action,priority|in:low,medium,high,critical,urgent',
        ]);

        try {
            $tickets = Tickets::whereIn('id', $request->ticket_ids)->get();
            $successCount = 0;
            $failedCount = 0;
            
            foreach ($tickets as $ticket) {
                try {
                    switch ($request->action) {
                        case 'assign':
                            // Skip closed or resolved tickets
                            if (in_array($ticket->status, ['closed', 'resolved'])) {
                                $failedCount++;
                                continue;
                            }

                            // Validate assigned user has proper role
                            $assignedUser = User::find($request->assigned_to);
                            if (!$assignedUser->hasAnyRole(['technician', 'admin', 'super-admin'])) {
                                $failedCount++;
                                continue;
                            }

                            // Determine status based on who is assigning
                            $assignerUser = $this->getCurrentUser();
                            $newStatus = 'assigned'; // Default status
                            
                            // If admin or super-admin is assigning, automatically set to 'in_progress'
                            if ($assignerUser->hasAnyRole(['admin', 'super-admin']) || $assignerUser->can('assign-tickets')) {
                                $newStatus = 'in_progress';
                            }
                            
                            $ticket->update([
                                'assigned_to' => $request->assigned_to, 
                                'status' => $newStatus,
                                'first_response_at' => $newStatus === 'in_progress' && !$ticket->first_response_at ? now() : $ticket->first_response_at,
                                'last_activity_at' => now(),
                            ]);

                            // Log bulk assignment
                            activity()
                                ->performedOn($ticket)
                                ->withProperties([
                                    'bulk_action' => 'assign',
                                    'assigned_to' => $request->assigned_to,
                                    'status_changed_to' => $newStatus,
                                    'assigned_by' => $assignerUser->id,
                                ])
                                ->log('Ticket bulk assigned with auto-status update');

                            $successCount++;
                            break;
                        case 'status':
                            $ticket->update(['status' => $request->status]);
                            $successCount++;
                            break;
                        case 'priority':
                            $ticket->update(['priority' => $request->priority]);
                            $successCount++;
                            break;
                        case 'delete':
                            $ticket->delete();
                            $successCount++;
                            break;
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Bulk operation failed for ticket', [
                        'ticket_id' => $ticket->id,
                        'action' => $request->action,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = "Bulk operation completed: {$successCount} tickets processed successfully";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} tickets failed";
            }

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            Log::error('Bulk operation failed', [
                'action' => $request->action,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Bulk operation failed. Please try again.'
            ], 500);
        }
    }

    public function downloadAttachment(TicketAttachment $attachment)
    {
        $this->authorize('view', $attachment->ticket);
        
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        $filePath = Storage::disk('public')->path($attachment->file_path);
        return response()->download($filePath, $attachment->original_name);
    }

    public function deleteAttachment(TicketAttachment $attachment)
    {
        $this->authorize('update', $attachment->ticket);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'Attachment deleted successfully']);
    }

    private function storeAttachment(Tickets $ticket, $file)
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('ticket-attachments', $filename, 'public');

        TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $filename,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $this->getCurrentUserId(),
        ]);
    }

    private function sendTicketCreatedNotifications(Tickets $ticket)
    {
        // Notify assigned technician if ticket is assigned
        if ($ticket->assigned_to) {
            $ticket->assignedTo->notify(new \App\Notifications\TicketCreated($ticket));
        }
        
        // Notify all managers and admins
        $managers = User::role(['manager', 'admin', 'super-admin'])->get();
        Notification::send($managers, new \App\Notifications\TicketCreated($ticket));
        
        // Notify all technicians about new ticket (they might want to claim it)
        $technicians = User::role('technician')->get();
        Notification::send($technicians, new \App\Notifications\TicketCreated($ticket));
    }

    private function sendTicketAssignedNotifications(Tickets $ticket)
    {
        if ($ticket->assigned_to) {
            $ticket->assignedTo->notify(new TicketAssignedNotification($ticket));
        }
        
        // Notify ticket creator
        $ticket->user->notify(new TicketAssignedNotification($ticket));
    }

    /**
     * Store attachment for a ticket
     */
    public function storeAttachments(Request $request, Tickets $ticket)
    {
        // Use the authorize method from the parent Controller class
        $this->middleware(['can:upload-ticket-attachments']);
        
        $request->validate([
            'attachments' => 'required',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip|max:10240',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->storeAttachment($ticket, $file);
            }
        }

        return redirect()->back()->with('success', 'Attachments uploaded successfully!');
    }

    // Helper method to get the current user ID safely
    private function getCurrentUserId()
    {
        return app('auth')->check() ? app('auth')->id() : null;
    }

    // Helper method to get the current user safely
    private function getCurrentUser()
    {
        return app('auth')->check() ? app('auth')->user() : null;
    }
}
