<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Tickets;
use App\Models\TicketCategory;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;

class Dashboard extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_tickets' => Tickets::count(),
            'open_tickets' => Tickets::where('status', 'open')->count(),
            'in_progress_tickets' => Tickets::where('status', 'in_progress')->count(),
            'closed_tickets' => Tickets::where('status', 'closed')->count(),
            'high_priority_tickets' => Tickets::where('priority', 'urgent')->count(),
            'overdue_tickets' => Tickets::overdue()->count(),
            'tickets_today' => Tickets::whereDate('created_at', today())->count(),
            'tickets_this_week' => Tickets::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'tickets_this_month' => Tickets::whereMonth('created_at', now()->month)->count(),
            'recent_activities' => Activity::latest()->limit(10)->get(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}