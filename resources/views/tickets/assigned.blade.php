@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Assigned Tickets</h1>
                <span class="badge bg-primary">{{ $tickets->total() }} Total</span>
            </div>

            @if($tickets->count() > 0)
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                <tr>
                                    <td>#{{ $ticket->id }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                            {{ $ticket->title }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $ticket->category->name ?? 'No Category' }}</span>
                                        @if($ticket->subcategory)
                                        <br><small class="text-muted">{{ $ticket->subcategory->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'success') }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $ticket->status === 'open' ? 'success' : ($ticket->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->user->name }}</td>
                                    <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
            @else
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> No tickets assigned to you yet.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection