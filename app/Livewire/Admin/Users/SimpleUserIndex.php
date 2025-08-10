<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class SimpleUserIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $statusFilter = '';
    public $roleFilter = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectAll = false;
    public $selectedUsers = [];
    public $selectedUser = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->authorize('view-users');
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->users->pluck('id')->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function updatedSelectedUsers()
    {
        $this->selectAll = count($this->selectedUsers) === $this->users->count();
    }

    public function viewUser($userId)
    {
        $this->selectedUser = User::with('roles')->find($userId);
        $this->dispatch('show-user-modal');
    }

    public function toggleStatus($userId)
    {
        $this->authorize('edit-users');
        
        $user = User::find($userId);
        if (!$user) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'User not found.']);
            return;
        }

        $currentStatus = $user->status ?? 'active';
        $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
        
        $user->update(['status' => $newStatus]);

        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'User status updated successfully.']);
    }

    public function deleteUser($userId)
    {
        $this->authorize('delete-users');
        
        $user = User::find($userId);
        if (!$user) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'User not found.']);
            return;
        }

        if ($user->id === Auth::id()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'You cannot delete your own account.']);
            return;
        }

        $user->delete();
        $this->dispatch('show-alert', ['type' => 'success', 'message' => 'User deleted successfully.']);
    }

    public function deleteSelected()
    {
        $this->authorize('delete-users');
        
        if (empty($this->selectedUsers)) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No users selected.']);
            return;
        }

        if (in_array(Auth::id(), $this->selectedUsers)) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'You cannot delete your own account.']);
            return;
        }

        $deletedCount = count($this->selectedUsers);
        User::whereIn('id', $this->selectedUsers)->delete();
        $this->selectedUsers = [];
        $this->selectAll = false;
        
        $this->dispatch('show-alert', ['type' => 'success', 'message' => $deletedCount . ' users deleted successfully.']);
    }

    public function getUsersProperty()
    {
        return User::with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where(function ($q) {
                        $q->where('status', 'active')
                          ->orWhereNull('status');
                    });
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        $roles = Role::orderBy('name')->get();

         $users = User::with(['roles'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.users.simple-user-index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}