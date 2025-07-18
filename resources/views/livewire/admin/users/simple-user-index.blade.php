<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">User Management</h1>
        @can('create-users')
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Add New User
        </a>
        @endcan
    </div>

    <!-- Flash Messages from Controller (Create/Edit User) -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="controller-success-alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="controller-error-alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Dynamic Alert Container for Livewire Actions -->
    <div id="livewire-alerts"></div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               placeholder="Search users by name, email..." 
                               wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="roleFilter">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Users ({{ $users->total() }})</h5>
                <div class="d-flex gap-2">
                    <!-- Per Page Selection -->
                    <select class="form-select form-select-sm" wire:model.live="perPage" style="width: auto;">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                    
                    @can('delete-users')
                    <button class="btn btn-sm btn-danger" 
                            wire:click="deleteSelected"
                            wire:confirm="Are you sure you want to delete selected users?"
                            @if(count($selectedUsers) === 0) disabled @endif>
                        <i class="bi bi-trash"></i> Delete Selected ({{ count($selectedUsers) }})
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            @can('delete-users')
                            <th width="40">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       wire:model.live="selectAll">
                            </th>
                            @endcan
                            <th width="60">
                                <button class="btn btn-sm btn-link p-0 text-decoration-none" 
                                        wire:click="sortBy('id')">
                                    ID
                                    @if($sortField === 'id')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </button>
                            </th>
                            <th>
                                <button class="btn btn-sm btn-link p-0 text-decoration-none" 
                                        wire:click="sortBy('name')">
                                    Name
                                    @if($sortField === 'name')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </button>
                            </th>
                            <th>
                                <button class="btn btn-sm btn-link p-0 text-decoration-none" 
                                        wire:click="sortBy('email')">
                                    Email
                                    @if($sortField === 'email')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </button>
                            </th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>
                                <button class="btn btn-sm btn-link p-0 text-decoration-none" 
                                        wire:click="sortBy('created_at')">
                                    Created
                                    @if($sortField === 'created_at')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </button>
                            </th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                @can('delete-users')
                                <td>
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           wire:model.live="selectedUsers" 
                                           value="{{ $user->id }}">
                                </td>
                                @endcan
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-primary rounded-circle text-white">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            @if($user->email_verified_at)
                                                <i class="bi bi-check-circle-fill text-success" title="Verified"></i>
                                            @else
                                                <i class="bi bi-exclamation-circle-fill text-warning" title="Not Verified"></i>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-info me-1">{{ $role->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary">No Role</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->status === 'active' || !$user->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- View Button -->
                                        <button class="btn btn-sm btn-outline-info" 
                                                wire:click="viewUser({{ $user->id }})"
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        @can('edit-users')
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit User">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan

                                        <!-- Status Toggle -->
                                        @can('edit-users')
                                        <button class="btn btn-sm btn-outline-{{ ($user->status === 'active' || !$user->status) ? 'warning' : 'success' }}" 
                                                wire:click="toggleStatus({{ $user->id }})"
                                                title="{{ ($user->status === 'active' || !$user->status) ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ ($user->status === 'active' || !$user->status) ? 'pause' : 'play' }}"></i>
                                        </button>
                                        @endcan

                                        <!-- Delete Button -->
                                        @can('delete-users')
                                        <button class="btn btn-sm btn-outline-danger" 
                                                wire:click="deleteUser({{ $user->id }})"
                                                wire:confirm="Are you sure you want to delete this user?"
                                                title="Delete User">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @php
                                    $colspanCount = auth()->user()->can('delete-users') ? 8 : 7;
                                @endphp
                                <td colspan="{{ $colspanCount }}" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4"></i>
                                        <p class="mt-2">No users found</p>
                                        @if($search)
                                            <p class="small">Try adjusting your search criteria</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedUser)
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Basic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td>{{ $selectedUser->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $selectedUser->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ ($selectedUser->status === 'active' || !$selectedUser->status) ? 'success' : 'danger' }}">
                                                {{ $selectedUser->status ?? 'active' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email Verified:</strong></td>
                                        <td>
                                            @if($selectedUser->email_verified_at)
                                                <span class="badge bg-success">Yes</span>
                                                <small class="text-muted">({{ $selectedUser->email_verified_at->format('M d, Y') }})</small>
                                            @else
                                                <span class="badge bg-warning">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Roles & Permissions</h6>
                                @if($selectedUser->roles->count() > 0)
                                    @foreach($selectedUser->roles as $role)
                                        <span class="badge bg-info me-1 mb-1">{{ $role->name }}</span>
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary">No Role Assigned</span>
                                @endif

                                <h6 class="mt-3">Account Info</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td>{{ $selectedUser->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td>{{ $selectedUser->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if($selectedUser)
                        @can('edit-users')
                        <a href="{{ route('admin.users.edit', $selectedUser) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit User
                        </a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Styles -->
    <style>
        .avatar-sm {
            width: 32px;
            height: 32px;
        }

        .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }
    </style>

    <!-- Scripts -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-user-modal', () => {
                const modal = new bootstrap.Modal(document.getElementById('userModal'));
                modal.show();
            });

            // Handle Livewire flash messages
            Livewire.on('show-alert', (data) => {
                const alertsContainer = document.getElementById('livewire-alerts');
                const alertHtml = `
                    <div class="alert alert-${data[0].type} alert-dismissible fade show" role="alert">
                        ${data[0].message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                alertsContainer.innerHTML = alertHtml;
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    alertsContainer.innerHTML = '';
                }, 5000);
            });

            // Auto-hide controller flash messages after 5 seconds
            setTimeout(() => {
                const controllerSuccess = document.getElementById('controller-success-alert');
                const controllerError = document.getElementById('controller-error-alert');
                
                if (controllerSuccess) {
                    controllerSuccess.remove();
                }
                if (controllerError) {
                    controllerError.remove();
                }
            }, 5000);
        });
    </script>
</div>