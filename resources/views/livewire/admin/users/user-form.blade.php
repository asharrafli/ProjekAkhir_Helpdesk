<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $isEdit ? 'Edit User' : 'Create New User' }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form wire:submit="save">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $isEdit ? 'User Information' : 'New User Details' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" wire:model="name" placeholder="Enter full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" wire:model="email" placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" wire:model="phone" placeholder="Enter phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" wire:model="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Password {{ $isEdit ? '(Leave empty to keep current)' : '*' }}
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" wire:model="password" placeholder="Enter password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" wire:model="password_confirmation" placeholder="Confirm password">
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_admin" wire:model="is_admin">
                                <label class="form-check-label" for="is_admin">
                                    <strong>Administrator Access</strong>
                                    <small class="text-muted d-block">Grant administrative privileges to this user</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">User Roles</h5>
                        <small class="text-muted">Select the roles to assign to this user</small>
                    </div>
                    <div class="card-body">
                        @if($roles->count() > 0)
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="role_{{ $role->id }}" 
                                                   value="{{ $role->name }}"
                                                   wire:model="selectedRoles">
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                <strong>{{ ucfirst($role->name) }}</strong>
                                                @if($role->permissions->count() > 0)
                                                    <small class="text-muted d-block">
                                                        {{ $role->permissions->count() }} permissions
                                                    </small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No roles available. Create roles first in the Roles section.</p>
                        @endif
                        
                        @error('selectedRoles')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check"></i> {{ $isEdit ? 'Update User' : 'Create User' }}
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <!-- Help Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">User Management Help</h6>
                </div>
                <div class="card-body">
                    <h6>User Status:</h6>
                    <ul class="small">
                        <li><strong>Active:</strong> User can login and access the system</li>
                        <li><strong>Inactive:</strong> User account is disabled</li>
                        <li><strong>Suspended:</strong> User account is temporarily blocked</li>
                    </ul>

                    <h6 class="mt-3">Administrator Access:</h6>
                    <p class="small">Checking this option grants the user administrative privileges. Admin users can:</p>
                    <ul class="small">
                        <li>Access admin dashboard</li>
                        <li>Manage other users (based on permissions)</li>
                        <li>View system analytics</li>
                    </ul>

                    <h6 class="mt-3">Roles vs Admin:</h6>
                    <p class="small">
                        Roles provide granular permissions, while Admin access provides broader system access. 
                        You can assign both roles and admin status for maximum flexibility.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>