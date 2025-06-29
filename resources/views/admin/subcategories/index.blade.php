@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Ticket Subcategories</h2>
                <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Subcategory
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Sort Order</th>
                                    <th>Tickets Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subcategories as $subcategory)
                                <tr>
                                    <td>{{ $subcategory->name }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $subcategory->category->name }}</span>
                                    </td>
                                    <td>{{ Str::limit($subcategory->description, 50) }}</td>
                                    <td>
                                        @if($subcategory->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $subcategory->sort_order }}</td>
                                    <td>{{ $subcategory->tickets_count }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.subcategories.edit', $subcategory) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($subcategory->tickets_count == 0)
                                            <form action="{{ route('admin.subcategories.destroy', $subcategory) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this subcategory?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No subcategories found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($subcategories->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $subcategories->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection