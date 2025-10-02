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
                            
                            <!-- Drop Zone -->
                            <div id="drop-zone" class="border border-2 border-dashed rounded p-4 text-center mb-3" 
                                 style="border-color: #dee2e6; min-height: 120px; transition: all 0.3s ease;">
                                <div id="drop-zone-content">
                                    <i class="bi bi-cloud-upload fs-1 text-muted mb-2"></i>
                                    <p class="mb-2 text-muted">Drag and drop files here or click to browse</p>
                                    <input type="file" name="attachments[]" id="attachments" 
                                           class="form-control d-none @error('attachments.*') is-invalid @enderror" 
                                           multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip,.bmp,.svg,.webp">
                                    <button type="button" id="browse-btn" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-folder2-open"></i> Browse Files
                                    </button>
                                </div>
                            </div>
                            
                            @error('attachments.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            
                            <!-- File Preview Area -->
                            <div id="file-preview" class="mt-3" style="display: none;">
                                <h6>Selected Files:</h6>
                                <div id="file-list" class="row g-2"></div>
                            </div>
                            
                            <div class="form-text">
                                <strong>Supported formats:</strong> Images (JPG, PNG, GIF, BMP, SVG, WebP), Documents (PDF, DOC, DOCX, TXT), Archives (ZIP).<br>
                                <strong>Maximum file size:</strong> 10MB per file. <strong>Maximum files:</strong> 10 files.
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
<style>
    #drop-zone {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    #drop-zone:hover {
        border-color: #0d6efd !important;
        background-color: #f8f9ff !important;
    }
    
    .file-preview-card {
        transition: transform 0.2s ease;
    }
    
    .file-preview-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .file-remove-btn {
        transition: all 0.2s ease;
    }
    
    .file-remove-btn:hover {
        transform: scale(1.05);
    }
    
    #file-preview img {
        border-radius: 4px;
    }
</style>
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
    
    // Enhanced file upload with drag & drop and preview
    const attachmentsInput = document.getElementById('attachments');
    const dropZone = document.getElementById('drop-zone');
    const browseBtn = document.getElementById('browse-btn');
    const filePreview = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');
    let selectedFiles = [];
    
    if (attachmentsInput && dropZone) {
        // Browse button click
        browseBtn.addEventListener('click', function() {
            attachmentsInput.click();
        });
        
        // File input change
        attachmentsInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
        
        // Drag and drop events
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#0d6efd';
            this.style.backgroundColor = '#f8f9ff';
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = 'transparent';
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = 'transparent';
            
            const files = e.dataTransfer.files;
            handleFiles(files);
        });
        
        // Click on drop zone to browse
        dropZone.addEventListener('click', function(e) {
            if (e.target === this || e.target.closest('#drop-zone-content')) {
                attachmentsInput.click();
            }
        });
        
        function handleFiles(files) {
            const maxFiles = 10;
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/svg+xml', 'image/webp', 
                                 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                                 'text/plain', 'application/zip'];
            
            // Check total files limit
            if (selectedFiles.length + files.length > maxFiles) {
                alert(`Maximum ${maxFiles} files allowed. You have ${selectedFiles.length} files already selected.`);
                return;
            }
            
            let validFiles = [];
            let errorMessages = [];
            
            Array.from(files).forEach(file => {
                // Check file size
                if (file.size > maxSize) {
                    errorMessages.push(`${file.name} is too large (${formatFileSize(file.size)}). Maximum size is 10MB.`);
                    return;
                }
                
                // Check file type
                if (!allowedTypes.includes(file.type)) {
                    errorMessages.push(`${file.name} is not a supported file type.`);
                    return;
                }
                
                // Check for duplicates
                const isDuplicate = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (isDuplicate) {
                    errorMessages.push(`${file.name} is already selected.`);
                    return;
                }
                
                validFiles.push(file);
            });
            
            if (errorMessages.length > 0) {
                alert('Some files were not added:\n\n' + errorMessages.join('\n'));
            }
            
            if (validFiles.length > 0) {
                selectedFiles.push(...validFiles);
                updateFilePreview();
                updateFileInput();
            }
        }
        
        function updateFilePreview() {
            if (selectedFiles.length === 0) {
                filePreview.style.display = 'none';
                return;
            }
            
            filePreview.style.display = 'block';
            fileList.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'col-md-6 col-lg-4';
                
                const isImage = file.type.startsWith('image/');
                let previewContent = '';
                
                if (isImage) {
                    const fileURL = URL.createObjectURL(file);
                    previewContent = `
                        <div class="card h-100">
                            <img src="${fileURL}" class="card-img-top" style="height: 120px; object-fit: cover;">
                            <div class="card-body p-2">
                                <h6 class="card-title small mb-1" title="${file.name}">${truncateFileName(file.name, 20)}</h6>
                                <p class="card-text small text-muted mb-2">${formatFileSize(file.size)}</p>
                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeFile(${index})">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    const fileIcon = getFileIcon(file.type);
                    previewContent = `
                        <div class="card h-100">
                            <div class="card-body text-center p-3">
                                <i class="bi ${fileIcon} fs-1 text-primary mb-2"></i>
                                <h6 class="card-title small mb-1" title="${file.name}">${truncateFileName(file.name, 20)}</h6>
                                <p class="card-text small text-muted mb-2">${formatFileSize(file.size)}</p>
                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeFile(${index})">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    `;
                }
                
                fileItem.innerHTML = previewContent;
                fileList.appendChild(fileItem);
            });
        }
        
        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => {
                dt.items.add(file);
            });
            attachmentsInput.files = dt.files;
        }
        
        // Global function to remove file
        window.removeFile = function(index) {
            selectedFiles.splice(index, 1);
            updateFilePreview();
            updateFileInput();
        };
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function truncateFileName(name, maxLength) {
            if (name.length <= maxLength) return name;
            const extension = name.split('.').pop();
            const nameWithoutExt = name.substring(0, name.lastIndexOf('.'));
            const truncatedName = nameWithoutExt.substring(0, maxLength - extension.length - 4) + '...';
            return truncatedName + '.' + extension;
        }
        
        function getFileIcon(mimeType) {
            if (mimeType.includes('pdf')) return 'bi-file-earmark-pdf';
            if (mimeType.includes('word') || mimeType.includes('document')) return 'bi-file-earmark-word';
            if (mimeType.includes('text')) return 'bi-file-earmark-text';
            if (mimeType.includes('zip')) return 'bi-file-earmark-zip';
            return 'bi-file-earmark';
        }
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