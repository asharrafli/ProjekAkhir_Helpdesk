{{-- filepath: resources/views/livewire/admin/users/show-user.blade.php --}}
<div>
    {{-- ✅ Single root div element --}}
    
    <!-- Styles moved to top inside the root div -->
    <style>
    .avatar-lg {
        width: 80px;
        height: 80px;
    }

    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    </style>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">User Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.users.index') }}">User Management</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $user->name ?? 'User' }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
                @can('edit-users')
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit User
                </a>
                @endcan
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- User Information -->
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-primary rounded-circle text-white fs-1">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                            </div>
                        </div>
                        <h4 class="mb-1">{{ $user->name ?? 'N/A' }}</h4>
                        <p class="text-muted mb-3">{{ $user->email ?? 'N/A' }}</p>
                        
                        <!-- Status Badge -->
                        <div class="mb-3">
                            @php
                                $userStatus = $user->status ?? 'active';
                            @endphp
                            @if($userStatus === 'active')
                                <span class="badge bg-success fs-6">Active</span>
                            @else
                                <span class="badge bg-danger fs-6">Inactive</span>
                            @endif
                            
                            @if($user->email_verified_at)
                                <span class="badge bg-info fs-6 ms-1">Verified</span>
                            @else
                                <span class="badge bg-warning fs-6 ms-1">Unverified</span>
                            @endif
                        </div>

                        <!-- Roles -->
                        <div class="mb-3">
                            <h6 class="text-muted">Roles</h6>
                            @if($user->roles && $user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <span class="badge bg-info me-1 mb-1">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            @else
                                <span class="badge bg-secondary">No Role Assigned</span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        @can('edit-users')
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-{{ $userStatus === 'active' ? 'warning' : 'success' }}" 
                                    wire:click="toggleStatus"
                                    wire:confirm="Are you sure you want to change this user's status?">
                                <i class="bi bi-{{ $userStatus === 'active' ? 'pause' : 'play' }}"></i>
                                {{ $userStatus === 'active' ? 'Deactivate' : 'Activate' }} User
                            </button>
                            
                            @can('delete-users')
                            @if($user->id !== auth()->id())
                            <button class="btn btn-outline-danger" 
                                    wire:click="deleteUser"
                                    wire:confirm="Are you sure you want to delete this user? This action cannot be undone!">
                                <i class="bi bi-trash"></i> Delete User
                            </button>
                            @endif
                            @endcan
                        </div>
                        @endcan
                    </div>
                </div>

                <!-- Account Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>User ID:</strong></div>
                            <div class="col-sm-7">#{{ $user->id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Email:</strong></div>
                            <div class="col-sm-7">{{ $user->email }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Joined:</strong></div>
                            <div class="col-sm-7">
                                {{ $user->created_at->format('M d, Y') }}
                                <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Last Update:</strong></div>
                            <div class="col-sm-7">
                                {{ $user->updated_at->format('M d, Y') }}
                                <small class="text-muted d-block">{{ $user->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @if($user->email_verified_at)
                        <div class="row mb-3">
                            <div class="col-sm-5"><strong>Verified:</strong></div>
                            <div class="col-sm-7">
                                {{ $user->email_verified_at->format('M d, Y') }}
                                <small class="text-muted d-block">{{ $user->email_verified_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics and Activities -->
            <div class="col-lg-8">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="text-primary mb-2">
                                    <i class="bi bi-ticket-perforated fs-1"></i>
                                </div>
                                <h4 class="mb-0">{{ $userStats['total_tickets'] ?? 0 }}</h4>
                                <small class="text-muted">Total Tickets</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="text-warning mb-2">
                                    <i class="bi bi-clock fs-1"></i>
                                </div>
                                <h4 class="mb-0">{{ $userStats['open_tickets'] ?? 0 }}</h4>
                                <small class="text-muted">Open Tickets</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="text-success mb-2">
                                    <i class="bi bi-check-circle fs-1"></i>
                                </div>
                                <h4 class="mb-0">{{ $userStats['resolved_tickets'] ?? 0 }}</h4>
                                <small class="text-muted">Resolved</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="text-info mb-2">
                                    <i class="bi bi-person-check fs-1"></i>
                                </div>
                                <h4 class="mb-0">{{ $userStats['assigned_tickets'] ?? 0 }}</h4>
                                <small class="text-muted">Assigned</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                @if($user->roles && $user->roles->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Permissions</h5>
                    </div>
                    <div class="card-body">
                        @foreach($user->roles as $role)
                            <h6 class="text-muted">{{ ucfirst($role->name) }} Permissions:</h6>
                            <div class="row mb-3">
                                @forelse($role->permissions as $permission)
                                    <div class="col-md-4 col-sm-6 mb-1">
                                        <span class="badge bg-light text-dark border">{{ $permission->name }}</span>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <span class="text-muted">No specific permissions assigned</span>
                                    </div>
                                @endforelse
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Recent Tickets -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Tickets</h5>
                        {{-- ✅ Fix: Check if tickets route exists --}}
                        @if(Route::has('admin.tickets.index'))
                            <a href="{{ route('tickets.index') }}?user_id={{ $user->id }}" class="btn btn-sm btn-outline-primary">
                                View All Tickets
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        @forelse($recentActivities as $ticket)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <h6 class="mb-1">
                                        {{-- ✅ Fix: Check if ticket show route exists --}}
                                        @if(Route::has('tickets.show'))
                                            <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                                {{ $ticket->title ?? $ticket->title_ticket ?? 'No Title' }}
                                            </a>
                                        @else
                                            {{ $ticket->title ?? $ticket->title_ticket ?? 'No Title' }}
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        {{ $ticket->category->name ?? 'No Category' }} • 
                                        {{ $ticket->created_at->format('M d, Y') }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ 
                                        $ticket->status === 'open' ? 'warning' : 
                                        ($ticket->status === 'resolved' ? 'success' : 'secondary') 
                                    }}">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                    <div class="small text-muted">
                                        Priority: {{ ucfirst($ticket->priority ?? 'normal') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-ticket-perforated display-4 text-muted"></i>
                                <p class="text-muted mt-2">No tickets found for this user</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
