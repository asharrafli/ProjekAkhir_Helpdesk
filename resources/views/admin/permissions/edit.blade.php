@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Edit Permission: {{ $permission->name }}</h2>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Permissions
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $permission->name) }}" 
                                           placeholder="e.g., manage-users, view-reports" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Use kebab-case format (e.g., view-users, create-tickets)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Guard Name</label>
                                    <input type="text" class="form-control" value="{{ $permission->guard_name }}" readonly>
                                    <small class="text-muted">Guard name cannot be changed.</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Permission
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection