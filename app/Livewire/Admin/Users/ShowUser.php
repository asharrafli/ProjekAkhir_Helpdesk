<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShowUser extends Component
{
    use AuthorizesRequests;

    public $user;
    public $userStats = [];
    public $recentActivities = [];

    // ✅ Fix: Gunakan User model binding sesuai route {user}
    public function mount(User $user)
    {
        try {
            $this->authorize('view-users');
        } catch (\Exception $e) {
            abort(403, 'You are not authorized to view users.');
        }
        
        // ✅ Set user dari route model binding
        $this->user = $user;
        $this->loadUserData();
    }

    private function loadUserData()
    {
        // Load relationships
        $this->user->load(['roles.permissions', 'tickets.category']);
        $this->calculateUserStats();
        $this->loadRecentActivities();
    }

    private function calculateUserStats()
    {
        if (!$this->user) {
            $this->userStats = [
                'total_tickets' => 0,
                'open_tickets' => 0,
                'resolved_tickets' => 0,
                'assigned_tickets' => 0,
            ];
            return;
        }

        $this->userStats = [
            'total_tickets' => $this->user->tickets()->count(),
            'open_tickets' => $this->user->tickets()->whereIn('status', ['open', 'pending'])->count(),
            'resolved_tickets' => $this->user->tickets()->whereIn('status', ['resolved', 'closed'])->count(),
            'assigned_tickets' => method_exists($this->user, 'assignedTickets') 
                ? $this->user->assignedTickets()->count() 
                : 0,
        ];
    }

    private function loadRecentActivities()
    {
        if (!$this->user) {
            $this->recentActivities = collect([]);
            return;
        }

        try {
            $this->recentActivities = $this->user->tickets()
                ->with(['category', 'assignedTo'])
                ->latest('updated_at')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            $this->recentActivities = collect([]);
        }
    }

    public function toggleStatus()
    {
        try {
            $this->authorize('edit-users');
        } catch (\Exception $e) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }
        
        if (!$this->user) {
            session()->flash('error', 'User not found.');
            return;
        }

        try {
            $currentStatus = $this->user->status ?? 'active';
            $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
            
            $this->user->update(['status' => $newStatus]);
            $this->loadUserData();
            
            session()->flash('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update user status.');
        }
    }

    public function deleteUser()
    {
        try {
            $this->authorize('delete-users');
        } catch (\Exception $e) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }
        
        if (!$this->user) {
            session()->flash('error', 'User not found.');
            return;
        }

        if ($this->user->id === Auth::id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        try {
            $userName = $this->user->name;
            $this->user->delete();
            
            session()->flash('success', "User '{$userName}' deleted successfully.");
            return redirect()->route('admin.users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete user. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.admin.users.show-user')
        ->layout('layouts.app');
        
    }
}
