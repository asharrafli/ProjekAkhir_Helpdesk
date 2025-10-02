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
                    @php
                        $attachments = $ticket->attachments()->get();
                    @endphp
                    @if($attachments && $attachments->count() > 0)
                    <div class="mb-4">
                        <h5><i class="bi bi-paperclip"></i> Attachments ({{ $attachments->count() }})</h5>
                        <div class="row g-3">
                            @foreach($attachments as $attachment)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    @if($attachment->isImage())
                                    <!-- Image Preview -->
                                    <div class="card-img-top" style="height: 200px; overflow: hidden;">
                                        <img src="{{ $attachment->getFileUrl() }}" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;"
                                             alt="{{ $attachment->original_name }}"
                                             onclick="showImageModal('{{ $attachment->getFileUrl() }}', '{{ $attachment->original_name }}')">
                                    </div>
                                    @else
                                    <!-- Document Icon -->
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                        @if(str_contains($attachment->mime_type, 'pdf'))
                                            <i class="bi bi-file-earmark-pdf display-1 text-danger"></i>
                                        @elseif(str_contains($attachment->mime_type, 'word') || str_contains($attachment->mime_type, 'document'))
                                            <i class="bi bi-file-earmark-word display-1 text-primary"></i>
                                        @elseif(str_contains($attachment->mime_type, 'text'))
                                            <i class="bi bi-file-earmark-text display-1 text-info"></i>
                                        @elseif(str_contains($attachment->mime_type, 'zip'))
                                            <i class="bi bi-file-earmark-zip display-1 text-warning"></i>
                                        @else
                                            <i class="bi bi-file-earmark display-1 text-secondary"></i>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    <div class="card-body">
                                        <h6 class="card-title" title="{{ $attachment->original_name }}">
                                            {{ Str::limit($attachment->original_name, 25) }}
                                        </h6>
                                        <p class="card-text small text-muted mb-2">
                                            <strong>Size:</strong> {{ $attachment->getFormattedFileSize() }}<br>
                                            <strong>Uploaded:</strong> {{ $attachment->created_at->format('M j, Y') }}<br>
                                            <strong>By:</strong> {{ $attachment->uploadedBy->name }}
                                        </p>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex gap-2">
                                            <a href="{{ $attachment->getDownloadUrl() }}" 
                                               class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                            @can('delete-ticket-attachments')
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteAttachment({{ $attachment->id }})">
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
            <!-- Reply -->
         <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="bi bi-chat-dots"></i> Comments & Updates</h5>
                </div>
                <div class="card-body">
                    @if($ticket->comments->count() > 0)
                        <div class="comments-section">
                            @foreach($ticket->comments as $comment)
                                @if(!$comment->is_internal || Auth::user()->can('view-internal-notes'))
                                <div class="comment-item mb-4 {{ $comment->is_internal ? 'border-start border-warning border-3 ps-3' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $comment->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) . '&background=random' }}" 
                                                 class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                            <div>
                                                <strong>{{ $comment->user->name }}</strong>
                                                @if($comment->user->hasRole('technician'))
                                                    <span class="badge bg-primary ms-1">Tech</span>
                                                @elseif($comment->user->hasRole('admin'))
                                                    <span class="badge bg-danger ms-1">Admin</span>
                                                @endif
                                                @if($comment->is_internal)
                                                    <span class="badge bg-warning ms-1">Internal Note</span>
                                                @endif
                                                @if($comment->is_solution)
                                                    <span class="badge bg-success ms-1">Solution</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ $comment->created_at->format('M j, Y g:i A') }} ({{ $comment->created_at->diffForHumans() }})</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="comment-content {{ $comment->is_internal ? 'bg-warning bg-opacity-10 p-3 rounded' : '' }}">
                                        {!! nl2br(e($comment->comment)) !!}
                                    </div>
                                </div>
                                <hr>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">No comments yet. Be the first to comment!</p>
                    @endif

                    <!-- Add Comment Form -->
                    @can('comment-on-tickets')
                    <div class="mt-4">
                        <h6>Add a Comment</h6>
                        <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}">
                            @csrf
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" rows="4" 
                                          placeholder="Type your comment here..." required>{{ old('comment') }}</textarea>
                                @error('comment')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row mb-3">
                                @if(Auth::user()->can('view-internal-notes'))
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_internal" id="is_internal" value="1" {{ old('is_internal') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_internal">
                                            <i class="bi bi-lock-fill text-warning"></i> Internal Note
                                            <small class="text-muted d-block">Only visible to technicians and admins</small>
                                        </label>
                                    </div>
                                </div>
                                @endif
                                
                                @if(Auth::user()->hasRole(['technician', 'admin']) && $ticket->status != 'resolved' && $ticket->status != 'closed')
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_solution" id="is_solution" value="1" {{ old('is_solution') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_solution">
                                            <i class="bi bi-check-circle-fill text-success"></i> Mark as Solution
                                            <small class="text-muted d-block">This will resolve the ticket</small>
                                        </label>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Post Comment
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="document.querySelector('textarea[name=comment]').value=''">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i> You don't have permission to comment on tickets.
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Ticket Information -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> Information</h5>
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
                                    <div class="rounded-circle p-2 text-white {{ getActivityColor($activity->description) }}">
                                        <i class="{{ getActivityIcon($activity->description) }}" style="font-size: 0.75rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">{{ formatActivityDescription($activity->description, $activity->properties) }}</div>
                                    <div class="text-muted small">
                                        oleh {{ $activity->causer->name ?? 'System' }} • 
                                        {{ $activity->created_at->format('d M Y, H:i') }}
                                        <span class="text-muted">({{ $activity->created_at->diffForHumans() }})</span>
                                    </div>
                                    @if($activity->properties && $activity->properties->has('old') && $activity->properties->has('attributes'))
                                    <div class="mt-1">
                                        @foreach($activity->properties['attributes'] as $key => $newValue)
                                            @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $newValue)
                                            <small class="text-info">
                                                {{ ucfirst(str_replace('_', ' ', $key)) }}: 
                                                <span class="text-danger">{{ formatValue($activity->properties['old'][$key]) }}</span> 
                                                → 
                                                <span class="text-success">{{ formatValue($newValue) }}</span>
                                            </small><br>
                                            @endif
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-clock-history"></i><br>
                        Belum ada aktivitas yang tercatat.
                    </p>
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

@php
function formatActivityDescription($description, $properties = null) {
    $descriptions = [
        'created' => 'Tiket dibuat',
        'updated' => 'Tiket diperbarui',
        'assigned' => 'Tiket ditugaskan ke teknisi',
        'claimed' => 'Tiket diklaim oleh teknisi',
        'status_changed' => 'Status tiket diubah',
        'priority_changed' => 'Prioritas tiket diubah',
        'commented' => 'Komentar ditambahkan',
        'attachment_added' => 'File lampiran ditambahkan',
        'attachment_removed' => 'File lampiran dihapus',
        'escalated' => 'Tiket dieskalasi',
        'resolved' => 'Tiket diselesaikan',
        'closed' => 'Tiket ditutup',
        'reopened' => 'Tiket dibuka kembali',
        'category_changed' => 'Kategori tiket diubah',
        'subcategory_changed' => 'Subkategori tiket diubah',
        'description_updated' => 'Deskripsi tiket diperbarui',
        'title_updated' => 'Judul tiket diperbarui',
        'due_date_set' => 'Tanggal deadline ditetapkan',
        'due_date_updated' => 'Tanggal deadline diperbarui',
        'sla_breached' => 'SLA terlampaui',
        'notification_sent' => 'Notifikasi dikirim',
        'reminder_sent' => 'Pengingat dikirim',
    ];

    // Cek jika ada deskripsi khusus berdasarkan properties
    if ($properties && $properties->has('attributes')) {
        $attributes = $properties['attributes'];
        
        if (isset($attributes['assigned_to'])) {
            return 'Tiket ditugaskan ke teknisi';
        }
        
        if (isset($attributes['status'])) {
            $status = $attributes['status'];
            $statusLabels = [
                'open' => 'dibuka',
                'in_progress' => 'dalam proses',
                'pending' => 'menunggu',
                'resolved' => 'diselesaikan',
                'closed' => 'ditutup',
                'escalated' => 'dieskalasi'
            ];
            return 'Status tiket diubah menjadi ' . ($statusLabels[$status] ?? $status);
        }
        
        if (isset($attributes['priority'])) {
            $priority = $attributes['priority'];
            $priorityLabels = [
                'low' => 'rendah',
                'medium' => 'sedang',
                'high' => 'tinggi',
                'urgent' => 'mendesak'
            ];
            return 'Prioritas tiket diubah menjadi ' . ($priorityLabels[$priority] ?? $priority);
        }
    }

    return $descriptions[$description] ?? ucfirst(str_replace('_', ' ', $description));
}

function getActivityIcon($description) {
    $icons = [
        'created' => 'bi-plus-circle-fill',
        'updated' => 'bi-pencil-fill',
        'assigned' => 'bi-person-fill-add',
        'claimed' => 'bi-hand-thumbs-up-fill',
        'status_changed' => 'bi-arrow-repeat',
        'priority_changed' => 'bi-exclamation-triangle-fill',
        'commented' => 'bi-chat-fill',
        'attachment_added' => 'bi-paperclip',
        'attachment_removed' => 'bi-trash-fill',
        'escalated' => 'bi-arrow-up-circle-fill',
        'resolved' => 'bi-check-circle-fill',
        'closed' => 'bi-x-circle-fill',
        'reopened' => 'bi-arrow-clockwise',
        'category_changed' => 'bi-tags-fill',
        'subcategory_changed' => 'bi-tag-fill',
        'description_updated' => 'bi-file-text-fill',
        'title_updated' => 'bi-type',
        'due_date_set' => 'bi-calendar-plus-fill',
        'due_date_updated' => 'bi-calendar-check-fill',
        'sla_breached' => 'bi-exclamation-octagon-fill',
        'notification_sent' => 'bi-bell-fill',
        'reminder_sent' => 'bi-alarm-fill',
    ];

    return $icons[$description] ?? 'bi-circle-fill';
}

function getActivityColor($description) {
    $colors = [
        'created' => 'bg-primary',
        'updated' => 'bg-info',
        'assigned' => 'bg-success',
        'claimed' => 'bg-success',
        'status_changed' => 'bg-warning',
        'priority_changed' => 'bg-warning',
        'commented' => 'bg-secondary',
        'attachment_added' => 'bg-info',
        'attachment_removed' => 'bg-danger',
        'escalated' => 'bg-danger',
        'resolved' => 'bg-success',
        'closed' => 'bg-dark',
        'reopened' => 'bg-warning',
        'category_changed' => 'bg-info',
        'subcategory_changed' => 'bg-info',
        'description_updated' => 'bg-info',
        'title_updated' => 'bg-info',
        'due_date_set' => 'bg-primary',
        'due_date_updated' => 'bg-warning',
        'sla_breached' => 'bg-danger',
        'notification_sent' => 'bg-primary',
        'reminder_sent' => 'bg-warning',
    ];

    return $colors[$description] ?? 'bg-secondary';
}

function formatValue($value) {
    if (is_null($value)) {
        return 'Tidak ada';
    }
    
    // Format status
    $statusLabels = [
        'open' => 'Terbuka',
        'in_progress' => 'Dalam Proses', 
        'pending' => 'Menunggu',
        'resolved' => 'Selesai',
        'closed' => 'Ditutup',
        'escalated' => 'Dieskalasi'
    ];
    
    // Format priority
    $priorityLabels = [
        'low' => 'Rendah',
        'medium' => 'Sedang', 
        'high' => 'Tinggi',
        'urgent' => 'Mendesak'
    ];
    
    if (isset($statusLabels[$value])) {
        return $statusLabels[$value];
    }
    
    if (isset($priorityLabels[$value])) {
        return $priorityLabels[$value];
    }
    
    return ucfirst(str_replace('_', ' ', $value));
}
@endphp

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

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

// Function to show image in modal
function showImageModal(imageSrc, imageName) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModalLabel').textContent = imageName;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

// Function to delete attachment
function deleteAttachment(attachmentId) {
    if (confirm('Are you sure you want to delete this attachment? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/tickets/attachments') }}/${attachmentId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection