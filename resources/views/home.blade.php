@extends('layouts.app')

@section('content')
@can('view-admin-dashboard')
    <livewire:admin.dashboard />
@elsecan('view-manager-dashboard')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Manager Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-4">Welcome to your manager dashboard. Access comprehensive reports and analytics.</p>
                        <a href="{{ route('manager.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-chart-line me-2"></i>View Manager Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="container-fluid">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-header">
                    <h1 class="h3 mb-2">
                        @if(Auth::user()->hasRole('technician'))
                            Selamat Datang, Teknisi {{ Auth::user()->name }}!
                        @else
                            Selamat Datang, {{ Auth::user()->name }}!
                        @endif
                    </h1>
                    <p class="text-muted mb-0">
                        @if(Auth::user()->hasRole('technician'))
                            Kelola dan selesaikan tiket support dengan efisien
                        @else
                            Kelola tiket support Anda dengan mudah
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if(Auth::user()->hasRole('technician'))
            <!-- TEKNISI DASHBOARD -->
            @php
                $technicianStats = [
                    'assigned_tickets' => Auth::user()->assignedTickets()->count(),
                    'pending_tickets' => Auth::user()->assignedTickets()->where('status', 'pending')->count(),
                    'in_progress_tickets' => Auth::user()->assignedTickets()->where('status', 'in_progress')->count(),
                    'resolved_today' => Auth::user()->assignedTickets()->where('status', 'resolved')->whereDate('updated_at', today())->count(),
                    'overdue_tickets' => Auth::user()->assignedTickets()->where('due_date', '<', now())->whereNotIn('status', ['resolved', 'closed'])->count(),
                    'total_resolved' => Auth::user()->assignedTickets()->where('status', 'resolved')->count(),
                ];
                $recentAssignedTickets = Auth::user()->assignedTickets()->with(['user', 'category'])->latest()->limit(5)->get();
            @endphp

            <!-- Quick Stats for Technician -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="stats-card bg-primary text-white">
                        <div class="stats-content">
                            <div class="stats-icon">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                            <div class="stats-info">
                                <h3>{{ $technicianStats['assigned_tickets'] }}</h3>
                                <p>Total Assigned</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="stats-card bg-info text-white">
                        <div class="stats-content">
                            <div class="stats-icon">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>{{ $technicianStats['in_progress_tickets'] }}</h3>
                                <p>In Progress</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="stats-card bg-dark text-white">
                        <div class="stats-content">
                            <div class="stats-icon">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>{{ $technicianStats['total_resolved'] }}</h3>
                                <p>Total Resolved</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technician Quick Actions -->
            {{-- <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Tindakan Cepat untuk Teknisi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('tickets.assigned') }}" class="btn btn-primary btn-block">
                                        <i class="bi bi-ticket-detailed"></i> Tiket yang Ditugaskan
                                    </a>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('tickets.create') }}" class="btn btn-success btn-block">
                                        <i class="bi bi-plus-circle"></i> Buat Tiket Baru
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Recent Assigned Tickets -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Tiket yang Baru Ditugaskan</h5>
                        </div>
                        <div class="card-body">
                             <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('tickets.assigned') }}" class="btn btn-primary btn-block">
                                        <i class="bi bi-ticket-detailed"></i> Tiket yang Ditugaskan
                                    </a>
                                </div>
                            @if($recentAssignedTickets->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tiket #</th>
                                                <th>Judul</th>
                                                <th>Status</th>
                                                <th>Prioritas</th>
                                                <th>Dibuat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentAssignedTickets as $ticket)
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
                                <p class="text-muted">Tidak ada tiket baru yang ditugaskan kepada Anda.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- USER DASHBOARD -->
            <!-- CUSTOMER DASHBOARD -->
            @php
                $customerStats = [
                    'open_tickets' => Auth::user()->tickets()->where('status', 'open')->count(),
                    'in_progress_tickets' => Auth::user()->tickets()->where('status', 'in_progress')->count(),
                    'resolved_tickets' => Auth::user()->tickets()->where('status', 'resolved')->count(),
                ];
            @endphp

            <!-- Quick Stats for Customer -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="stats-card bg-warning text-white">
                        <div class="stats-content">
                            <div class="stats-icon">
                                <i class="bi bi-exclamation-circle-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>{{ $customerStats['open_tickets'] }}</h3>
                                <p>Tiket Terbuka</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="stats-card bg-info text-white">
                        <div class="stats-content">
                            <div class="stats-icon">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>{{ $customerStats['in_progress_tickets'] }}</h3>
                                <p>Dalam Proses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="stats-card bg-success text-white">
                        <div class="stats-content">
                            <div class="stats-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>{{ $customerStats['resolved_tickets'] }}</h3>
                                <p>Selesai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Dashboard Pengguna</h5>
                        </div>
                        <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <p class="mb-4">Selamat datang di sistem tiket. Berikut adalah yang dapat Anda lakukan:</p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <i class="bi bi-ticket-perforated fs-1 text-primary mb-3"></i>
                                            <h5>Buat Tiket</h5>
                                            <p class="text-muted">Kirim tiket dukungan baru</p>
                                            <a href="{{ route('tickets.create') }}" class="btn btn-primary">Buat Tiket</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <i class="bi bi-list-ul fs-1 text-info mb-3"></i>
                                            <h5>Tiket Saya</h5>
                                            <p class="text-muted">Lihat tiket yang telah Anda kirim</p>
                                            <a href="{{ route('tickets.index') }}" class="btn btn-info">Lihat Tiket</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User's recent tickets -->
                            <div class="mt-4">
                                <h5>Tiket Terakhir Anda</h5>
                                @php
                                    $userTickets = Auth::user()->tickets()->latest()->limit(5)->get();
                                @endphp
                                
                                @if($userTickets->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Tiket #</th>
                                                    <th>Judul</th>
                                                    <th>Status</th>
                                                    <th>Prioritas</th>
                                                    <th>Dibuat</th>
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
                                    <p class="text-muted">Anda belum membuat tiket apapun.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endcan
@endsection
