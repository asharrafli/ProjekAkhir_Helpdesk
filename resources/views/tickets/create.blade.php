@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Ticket</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required 
                                       placeholder="Brief description of the issue">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subcategory_id" class="form-label">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror">
                                    <option value="">Select Subcategory (Optional)</option>
                                    @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}" 
                                            {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                        {{ $subcategory->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description_ticket" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description_ticket" id="description_ticket" rows="6" 
                                      class="form-control @error('description_ticket') is-invalid @enderror" 
                                      required placeholder="Please provide detailed information about the issue...">{{ old('description_ticket') }}</textarea>
                            @error('description_ticket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Provide as much detail as possible to help us resolve your issue quickly.</div>
                        </div>

                        <!-- File Attachments -->
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input type="file" name="attachments[]" id="attachments" 
                                   class="form-control @error('attachments.*') is-invalid @enderror" 
                                   multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                You can upload multiple files. Supported formats: Images (JPG, PNG, GIF), Documents (PDF, DOC, DOCX, TXT), Archives (ZIP).
                                Maximum file size: 10MB per file.
                            </div>
                        </div>

                        <!-- Assignment (Admin/Manager only) -->
                        @can('assign-tickets')
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign to Technician</label>
                            <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">Leave Unassigned</option>
                                @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" {{ old('assigned_to') == $technician->id ? 'selected' : '' }}>
                                    {{ $technician->name }} ({{ $technician->email }})
                                </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endcan

                        <!-- Customer Information (Admin only) -->
                        @can('create-tickets-for-others')
                        <div class="border-top pt-3 mt-4">
                            <h5 class="mb-3">Customer Information</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" id="customer_name" 
                                           class="form-control @error('customer_name') is-invalid @enderror" 
                                           value="{{ old('customer_name', Auth::user()->name) }}">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_email" class="form-label">Customer Email</label>
                                    <input type="email" name="customer_email" id="customer_email" 
                                           class="form-control @error('customer_email') is-invalid @enderror" 
                                           value="{{ old('customer_email', Auth::user()->email) }}">
                                    @error('customer_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endcan

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Tickets
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Create Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Subcategory filtering based on category
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            const subcategoryOptions = subcategorySelect.querySelectorAll('option[data-category]');
            
            // Hide all subcategory options first
            subcategoryOptions.forEach(option => {
                option.style.display = 'none';
            });
            
            // Reset subcategory selection
            subcategorySelect.value = '';
            
            // Show relevant subcategories
            if (selectedCategory) {
                subcategoryOptions.forEach(option => {
                    if (option.getAttribute('data-category') === selectedCategory) {
                        option.style.display = 'block';
                    }
                });
            }
        });
        
        // Trigger change event on page load to filter subcategories
        categorySelect.dispatchEvent(new Event('change'));
    }
    
    // File upload preview
    const attachmentsInput = document.getElementById('attachments');
    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function() {
            const files = this.files;
            let fileInfo = '';
            
            if (files.length > 0) {
                fileInfo = `${files.length} file(s) selected: `;
                const fileNames = Array.from(files).map(file => file.name).join(', ');
                fileInfo += fileNames;
                
                // Check file sizes
                let oversizedFiles = [];
                Array.from(files).forEach(file => {
                    if (file.size > 10 * 1024 * 1024) { // 10MB
                        oversizedFiles.push(file.name);
                    }
                });
                
                if (oversizedFiles.length > 0) {
                    alert('The following files are too large (>10MB): ' + oversizedFiles.join(', '));
                }
            }
            
            // Show file info (you can create a div to display this)
            console.log(fileInfo);
        });
    }
    
    // Auto-resize textarea
    const textarea = document.getElementById('description_ticket');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});
</script>
@endpush
@endsection