@extends('layouts.app')

@section('content')
@can('view-admin-dashboard')
    <livewire:admin.dashboard />
@else
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Welcome, {{ Auth::user()->name }}!</h4>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <p class="mb-4">Welcome to the ticketing system. Here's what you can do:</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="bi bi-ticket-perforated fs-1 text-primary mb-3"></i>
                                        <h5>Create Ticket</h5>
                                        <p class="text-muted">Submit a new support ticket</p>
                                        <a href="{{ route('tickets.create') }}" class="btn btn-primary">Create Ticket</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="bi bi-list-ul fs-1 text-info mb-3"></i>
                                        <h5>My Tickets</h5>
                                        <p class="text-muted">View your submitted tickets</p>
                                        <a href="{{ route('tickets.index') }}" class="btn btn-info">View Tickets</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User's recent tickets -->
                        <div class="mt-4">
                            <h5>Your Recent Tickets</h5>
                            @php
                                $userTickets = Auth::user()->tickets()->latest()->limit(5)->get();
                            @endphp
                            
                            @if($userTickets->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Ticket #</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($userTickets as $ticket)
                                                <tr>
                                                    <td><strong>{{ $ticket->ticket_number }}</strong></td>
                                                    <td>{{ Str::limit($ticket->title_ticket, 50) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $ticket->status === 'open' ? 'warning' : ($ticket->status === 'closed' ? 'success' : 'info') }}">
                                                            {{ ucfirst($ticket->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $ticket->priority === 'urgent' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'secondary') }}">
                                                            {{ ucfirst($ticket->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">You haven't created any tickets yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan
@endsection
