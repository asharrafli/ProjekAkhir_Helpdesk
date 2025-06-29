@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-ticket-perforated"></i> Tickets</h1>
        @can('create-tickets')
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Ticket
        </a>
        @endcan
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('tickets.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="escalated" {{ request('status') == 'escalated' ? 'selected' : '' }}>Escalated</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by ticket number, title..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    @can('bulk-ticket-operations')
    <div class="card mb-4" id="bulkActionsCard" style="display: none;">
        <div class="card-body">
            <form id="bulkActionsForm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="bulk_action" class="form-label">Bulk Action</label>
                        <select name="bulk_action" id="bulk_action" class="form-select">
                            <option value="">Select Action</option>
                            <option value="assign">Assign to Technician</option>
                            <option value="status">Change Status</option>
                            <option value="priority">Change Priority</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="bulk_assign_field" style="display: none;">
                        <label for="bulk_assigned_to" class="form-label">Assign To</label>
                        <select name="bulk_assigned_to" id="bulk_assigned_to" class="form-select">
                            <option value="">Select Technician</option>
                            @foreach($technicians as $technician)
                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3" id="bulk_status_field" style="display: none;">
                        <label for="bulk_status" class="form-label">New Status</label>
                        <select name="bulk_status" id="bulk_status" class="form-select">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="assigned">Assigned</option>
                            <option value="closed">Closed</option>
                            <option value="resolved">Resolved</option>
                            <option value="pending">Pending</option>
                            <option value="escalated">Escalated</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="bulk_priority_field" style="display: none;">
                        <label for="bulk_priority" class="form-label">New Priority</label>
                        <select name="bulk_priority" id="bulk_priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-gear"></i> Apply to <span id="selectedCount">0</span> tickets
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <!-- Tickets List -->
    <div class="card">
        <div class="card-body">
            @if($tickets->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            @can('bulk-ticket-operations')
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            @endcan
                            <th>Ticket #</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Category</th>
                            <th>Customer</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            @can('bulk-ticket-operations')
                            <td>
                                <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}" 
                                       class="form-check-input ticket-checkbox">
                            </td>
                            @endcan
                            <td>
                                <strong>{{ $ticket->ticket_number }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                    {{ Str::limit($ticket->title, 50) }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-{{ $ticket->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $ticket->priority_color }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                            <td>{{ $ticket->user->name ?? 'N/A' }}</td>
                            <td>{{ $ticket->assignedTo->name ?? 'Unassigned' }}</td>
                            <td>{{ $ticket->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('edit-tickets')
                                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    @can('claim-tickets')
                                    @if($ticket->status == 'open' && !$ticket->assigned_to)
                                    <form method="POST" action="{{ route('tickets.claim', $ticket) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-hand-thumbs-up"></i> Claim
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-ticket-perforated text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">No tickets found</h5>
                <p class="text-muted">Try adjusting your search filters or create a new ticket.</p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bulk actions functionality
    const selectAll = document.getElementById('selectAll');
    const ticketCheckboxes = document.querySelectorAll('.ticket-checkbox');
    const bulkActionsCard = document.getElementById('bulkActionsCard');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionSelect = document.getElementById('bulk_action');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            ticketCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    ticketCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    if (bulkActionSelect) {
        bulkActionSelect.addEventListener('change', function() {
            // Hide all bulk fields
            document.getElementById('bulk_assign_field').style.display = 'none';
            document.getElementById('bulk_status_field').style.display = 'none';
            document.getElementById('bulk_priority_field').style.display = 'none';
            
            // Show relevant field
            if (this.value === 'assign') {
                document.getElementById('bulk_assign_field').style.display = 'block';
            } else if (this.value === 'status') {
                document.getElementById('bulk_status_field').style.display = 'block';
            } else if (this.value === 'priority') {
                document.getElementById('bulk_priority_field').style.display = 'block';
            }
        });
    }
    
    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.ticket-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (selectedCount) {
            selectedCount.textContent = count;
        }
        
        if (bulkActionsCard) {
            bulkActionsCard.style.display = count > 0 ? 'block' : 'none';
        }
    }
    
    // Bulk actions form submission
    const bulkActionsForm = document.getElementById('bulkActionsForm');
    if (bulkActionsForm) {
        bulkActionsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const checkedBoxes = document.querySelectorAll('.ticket-checkbox:checked');
            const action = bulkActionSelect.value;
            
            if (checkedBoxes.length === 0) {
                alert('Please select at least one ticket.');
                return;
            }
            
            if (!action) {
                alert('Please select an action.');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete the selected tickets? This action cannot be undone.')) {
                    return;
                }
            }
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('action', action);
            
            checkedBoxes.forEach(checkbox => {
                formData.append('ticket_ids[]', checkbox.value);
            });
            
            // Add specific field values
            if (action === 'assign') {
                formData.append('assigned_to', document.getElementById('bulk_assigned_to').value);
            } else if (action === 'status') {
                formData.append('status', document.getElementById('bulk_status').value);
            } else if (action === 'priority') {
                formData.append('priority', document.getElementById('bulk_priority').value);
            }
            
            fetch('{{ route("tickets.bulk-update") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update tickets'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
        });
    }
});
</script>
@endpush
@endsection