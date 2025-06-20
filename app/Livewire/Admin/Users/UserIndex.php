<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $roleFilter = '';
    public $perPage = 15;

    public $showDeleteModal = false;
    public $userToDelete = null;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function confirmDelete($userId)
    {
        $this->userToDelete = User::find($userId);
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        if ($this->userToDelete) {
            if ($this->userToDelete->id === auth()->id()) {
                session()->flash('error', 'You cannot delete your own account.');
                return;
            }

            $this->userToDelete->delete();
            session()->flash('success', 'User deleted successfully.');
        }

        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function toggleStatus($userId)
    {
        $user = User::find($userId);
        if ($user && $user->id !== auth()->id()) {
            $user->status = $user->status === 'active' ? 'inactive' : 'active';
            $user->save();
            
            session()->flash('success', 'User status updated successfully.');
        } else {
            session()->flash('error', 'Cannot change your own status.');
        }
    }

    public function render()
    {
        try {
            $query = User::query();

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            if ($this->roleFilter) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            }

            $users = $query->with('roles')->latest()->paginate($this->perPage);
            $roles = Role::all();

            return view('livewire.admin.users.user-index', compact('users', 'roles'));
        } catch (\Exception $e) {
            logger()->error('UserIndex render error: ' . $e->getMessage());
            
            // Fallback to simple query
            $users = User::with('roles')->latest()->paginate($this->perPage);
            $roles = Role::all();
            
            return view('livewire.admin.users.user-index', compact('users', 'roles'));
        }
    }
}