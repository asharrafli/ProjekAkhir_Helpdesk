@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil"></i> Edit Ticket: {{ $ticket->ticket_number }}
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title', $ticket->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('priority', $ticket->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="urgent" {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ old('status', $ticket->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="assigned" {{ old('status', $ticket->status) == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                    <option value="pending" {{ old('status', $ticket->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ old('status', $ticket->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="escalated" {{ old('status', $ticket->status) == 'escalated' ? 'selected' : '' }}>Escalated</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $ticket->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="subcategory_id" class="form-label">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror">
                                    <option value="">Select Subcategory (Optional)</option>
                                    @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}" 
                                            {{ old('subcategory_id', $ticket->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @can('assign-tickets')
                            <div class="col-md-6">
                                <label for="assigned_to" class="form-label">Assigned To</label>
                                <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                    <option value="">Unassigned</option>
                                    @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}" {{ old('assigned_to', $ticket->assigned_to) == $technician->id ? 'selected' : '' }}>
                                        {{ $technician->name }} ({{ $technician->email }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endcan
                        </div>

                        <div class="mb-3">
                            <label for="description_ticket" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description_ticket" id="description_ticket" rows="6" 
                                      class="form-control @error('description_ticket') is-invalid @enderror" 
                                      required>{{ old('description_ticket', $ticket->description_ticket) }}</textarea>
                            @error('description_ticket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Resolution Notes (for resolved/closed tickets) -->
                        <div class="mb-3" id="resolution_section" style="display: none;">
                            <label for="resolution_notes" class="form-label">Resolution Notes</label>
                            <textarea name="resolution_notes" id="resolution_notes" rows="4" 
                                      class="form-control @error('resolution_notes') is-invalid @enderror" 
                                      placeholder="Describe how the issue was resolved...">{{ old('resolution_notes', $ticket->resolution_notes) }}</textarea>
                            @error('resolution_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Required when marking ticket as resolved or closed.</div>
                        </div>

                        <!-- Current Attachments -->
                        @if($ticket->attachments && $ticket->attachments->count() > 0)
                        <div class="mb-3">
                            <h5>Current Attachments</h5>
                            <div class="row">
                                @foreach($ticket->attachments as $attachment)
                                <div class="col-md-6 mb-2">
                                    <div class="card card-body bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-file-earmark"></i>
                                                <strong>{{ $attachment->original_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $attachment->getFormattedFileSize() }}</small>
                                            </div>
                                            <div>
                                                <a href="{{ route('tickets.attachments.download', $attachment) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @can('delete-ticket-attachments')
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="removeAttachment({{ $attachment->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Add New Attachments -->
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Add New Attachments</label>
                            <input type="file" name="attachments[]" id="attachments" 
                                   class="form-control @error('attachments.*') is-invalid @enderror" 
                                   multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum file size: 10MB per file</div>
                        </div>

                        <!-- Escalation Section -->
                        @if($ticket->is_escalated)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>This ticket has been escalated</strong>
                            @if($ticket->escalated_at)
                            on {{ $ticket->escalated_at->format('M j, Y g:i A') }}
                            @endif
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Ticket
                            </a>
                            <div>
                                @if(!$ticket->is_escalated && $ticket->status != 'closed' && $ticket->status != 'resolved')
                                <button type="button" class="btn btn-warning me-2" onclick="escalateTicket()">
                                    <i class="bi bi-exclamation-triangle"></i> Escalate
                                </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Update Ticket
                                </button>
                            </div>
                        </div>

                        <!-- Hidden field for escalation -->
                        <input type="hidden" name="escalate" id="escalate_field" value="0">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Subcategory filtering
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            const subcategoryOptions = subcategorySelect.querySelectorAll('option[data-category]');
            
            subcategoryOptions.forEach(option => {
                option.style.display = 'none';
            });
            
            if (selectedCategory) {
                subcategoryOptions.forEach(option => {
                    if (option.getAttribute('data-category') === selectedCategory) {
                        option.style.display = 'block';
                    }
                });
            }
        });
        
        categorySelect.dispatchEvent(new Event('change'));
    }
    
    // Show/hide resolution notes based on status
    const statusSelect = document.getElementById('status');
    const resolutionSection = document.getElementById('resolution_section');
    
    if (statusSelect && resolutionSection) {
        function toggleResolutionSection() {
            const status = statusSelect.value;
            if (status === 'resolved' || status === 'closed') {
                resolutionSection.style.display = 'block';
                document.getElementById('resolution_notes').required = true;
            } else {
                resolutionSection.style.display = 'none';
                document.getElementById('resolution_notes').required = false;
            }
        }
        
        statusSelect.addEventListener('change', toggleResolutionSection);
        toggleResolutionSection(); // Call on page load
    }
});

function escalateTicket() {
    if (confirm('Are you sure you want to escalate this ticket? This action will notify management and cannot be undone.')) {
        document.getElementById('escalate_field').value = '1';
        document.querySelector('form').submit();
    }
}

function removeAttachment(attachmentId) {
    if (confirm('Are you sure you want to remove this attachment?')) {
        fetch(`/tickets/attachments/${attachmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing attachment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the attachment.');
        });
    }
}
</script>
@endpush
@endsection