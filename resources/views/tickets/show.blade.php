@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Ticket Details -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-ticket-perforated"></i> {{ $ticket->ticket_number }}
                    </h4>
                    <div>
                        <span class="badge bg-{{ $ticket->status_color }} me-2">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <span class="badge bg-{{ $ticket->priority_color }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <h3 class="mb-3">{{ $ticket->title }}</h3>
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <strong>Category:</strong><br>
                            <span class="text-muted">{{ $ticket->category->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Subcategory:</strong><br>
                            <span class="text-muted">{{ $ticket->subcategory->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Created:</strong><br>
                            <span class="text-muted">{{ $ticket->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Last Updated:</strong><br>
                            <span class="text-muted">{{ $ticket->updated_at->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>

                    <div class="border-start border-4 border-primary ps-3 mb-4">
                        <h5>Description</h5>
                        <p class="mb-0">{{ $ticket->description_ticket }}</p>
                    </div>

                    @if($ticket->resolution_notes)
                    <div class="border-start border-4 border-success ps-3 mb-4">
                        <h5>Resolution Notes</h5>
                        <p class="mb-0">{{ $ticket->resolution_notes }}</p>
                        @if($ticket->resolved_at)
                        <small class="text-muted">Resolved on {{ $ticket->resolved_at->format('M j, Y g:i A') }}</small>
                        @endif
                    </div>
                    @endif

                    <!-- File Attachments -->
                    @if($ticket->hasAttachments())
                    <div class="mb-4">
                        <h5><i class="bi bi-paperclip"></i> Attachments</h5>
                        <div class="row">
                            @foreach($ticket->attachments as $attachment)
                            <div class="col-md-6 mb-2">
                                <div class="card card-body bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-file-earmark"></i>
                                            <strong>{{ $attachment->original_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $attachment->getFormattedFileSize() }} • 
                                                Uploaded by {{ $attachment->uploadedBy->name }} • 
                                                {{ $attachment->created_at->format('M j, Y') }}
                                            </small>
                                        </div>
                                        <div>
                                            <a href="{{ route('tickets.attachments.download', $attachment) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @can('delete-ticket-attachments')
                                            <form method="POST" action="{{ route('tickets.attachments.delete', $attachment) }}" 
                                                  class="d-inline" onsubmit="return confirm('Delete this attachment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 flex-wrap">
                        @can('edit-tickets')
                        <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Ticket
                        </a>
                        @endcan

                        @can('claim-tickets')
                        @if($ticket->status == 'open' && !$ticket->assigned_to)
                        <form method="POST" action="{{ route('tickets.claim', $ticket) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-hand-thumbs-up"></i> Claim This Ticket
                            </button>
                        </form>
                        @endif
                        @endcan

                        @can('assign-tickets')
                        @if(!$ticket->assigned_to || $ticket->assigned_to != Auth::id())
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#assignModal">
                            <i class="bi bi-person-plus"></i> Assign Ticket
                        </button>
                        @endif
                        @endcan

                        @if($ticket->status != 'closed')
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i> Change Status
                            </button>
                            <ul class="dropdown-menu">
                                @if($ticket->status != 'in_progress')
                                <li>
                                    <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="dropdown-item">In Progress</button>
                                    </form>
                                </li>
                                @endif
                                @if($ticket->status != 'pending')
                                <li>
                                    <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="dropdown-item">Pending</button>
                                    </form>
                                </li>
                                @endif
                                @if($ticket->status != 'resolved')
                                <li><a class="dropdown-item" href="#" onclick="changeStatus('resolved')">Resolved</a></li>
                                @endif
                                @if($ticket->status != 'closed')
                                <li><a class="dropdown-item" href="#" onclick="changeStatus('closed')">Closed</a></li>
                                @endif
                                @if($ticket->status != 'escalated')
                                <li>
                                    <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="escalated">
                                        <button type="submit" class="dropdown-item text-warning">Escalate</button>
                                    </form>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Tickets
                        </a>
                    </div>
                </div>
            </div>

            <!-- Add Files Section -->
            @can('upload-ticket-attachments')
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="bi bi-plus-circle"></i> Add Files</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.attachments.store', $ticket) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="attachments[]" class="form-control" multiple 
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                            <div class="form-text">Maximum file size: 10MB per file</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Upload Files
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Ticket Information -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> Ticket Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Customer:</strong><br>
                        <div class="d-flex align-items-center">
                            <img src="{{ $ticket->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->user->name) . '&background=random' }}" 
                                 class="rounded-circle me-2" width="30" height="30" alt="Avatar">
                            <div>
                                {{ $ticket->user->name }}<br>
                                <small class="text-muted">{{ $ticket->user->email }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Assigned To:</strong><br>
                        @if($ticket->assignedTo)
                        <div class="d-flex align-items-center">
                            <img src="{{ $ticket->assignedTo->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->assignedTo->name) . '&background=random' }}" 
                                 class="rounded-circle me-2" width="30" height="30" alt="Avatar">
                            <div>
                                {{ $ticket->assignedTo->name }}<br>
                                <small class="text-muted">{{ $ticket->assignedTo->email }}</small>
                            </div>
                        </div>
                        @else
                        <span class="text-muted">Unassigned</span>
                        @endif
                    </div>

                    @if($ticket->first_response_at)
                    <div class="mb-3">
                        <strong>First Response:</strong><br>
                        <span class="text-muted">{{ $ticket->first_response_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @endif

                    @if($ticket->response_time_minutes)
                    <div class="mb-3">
                        <strong>Response Time:</strong><br>
                        <span class="text-muted">{{ $ticket->response_time_minutes }} minutes</span>
                    </div>
                    @endif

                    @if($ticket->is_escalated)
                    <div class="mb-3">
                        <strong>Escalated:</strong><br>
                        <span class="text-warning">{{ $ticket->escalated_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong>SLA Status:</strong><br>
                        @if($ticket->sla_data)
                        <span class="badge bg-info">Within SLA</span>
                        @else
                        <span class="badge bg-secondary">No SLA</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="bi bi-clock-history"></i> Activity Timeline</h5>
                </div>
                <div class="card-body">
                    @if($ticket->activities->count() > 0)
                    <div class="timeline">
                        @foreach($ticket->activities()->latest()->take(10)->get() as $activity)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-2 text-white">
                                        <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">{{ $activity->description }}</div>
                                    <div class="text-muted small">
                                        by {{ $activity->causer->name ?? 'System' }} • 
                                        {{ $activity->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">No activity recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
@can('assign-tickets')
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign to Technician</label>
                        <select name="assigned_to" id="assigned_to" class="form-select" required>
                            <option value="">Select Technician</option>
                            @foreach($technicians as $technician)
                            <option value="{{ $technician->id }}" {{ $ticket->assigned_to == $technician->id ? 'selected' : '' }}>
                                {{ $technician->name }} ({{ $technician->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assignment_notes" class="form-label">Assignment Notes (Optional)</label>
                        <textarea name="assignment_notes" id="assignment_notes" class="form-control" rows="3" 
                                  placeholder="Any special instructions or notes for the technician..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
function changeStatus(newStatus) {
    if (confirm(`Are you sure you want to change the status to "${newStatus.replace('_', ' ')}"?`)) {
        // For resolved and closed status, we need resolution notes
        if (['resolved', 'closed'].includes(newStatus)) {
            const notes = prompt("Please enter resolution notes:", "");
            if (notes === null) {
                return; // User canceled
            }
            
            if (notes.trim() === "") {
                alert("Resolution notes are required when resolving or closing a ticket.");
                return;
            }
            
            // Include resolution notes in the form
            submitStatusChange(newStatus, notes);
        } else {
            // For other statuses, just submit the form
            submitStatusChange(newStatus);
        }
    }
}

function submitStatusChange(newStatus, resolutionNotes = null) {
    console.log('Submitting status change to:', newStatus, 'with notes:', resolutionNotes);
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("tickets.update-status", $ticket) }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'PATCH';
    
    const statusField = document.createElement('input');
    statusField.type = 'hidden';
    statusField.name = 'status';
    statusField.value = newStatus;
    
    form.appendChild(csrfToken);
    form.appendChild(methodField);
    form.appendChild(statusField);
    
    if (resolutionNotes) {
        const notesField = document.createElement('input');
        notesField.type = 'hidden';
        notesField.name = 'resolution_notes';
        notesField.value = resolutionNotes;
        form.appendChild(notesField);
    }
    
    document.body.appendChild(form);
    
    // Add event listener for form submission
    form.addEventListener('submit', function(e) {
        console.log('Form being submitted...');
    });
    
    form.submit();
}
</script>
@endpush
@endsection