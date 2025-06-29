<div>
     <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Role Management (Simple)</h1>
        @can('create-users')
        <a href="#" class="btn btn-primary">
            <i class="bi bi-plus"></i> Add New Role
        </a>
        @endcan
    </div>
</div>
