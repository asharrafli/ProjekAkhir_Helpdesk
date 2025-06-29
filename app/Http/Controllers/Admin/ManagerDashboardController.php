<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tickets;
use App\Models\User;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view-manager-dashboard']);
    }

    public function index()
    {
        $dashboardData = $this->getDashboardData();
        
        return view('admin.manager.dashboard', compact('dashboardData'));
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', 'week');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        switch ($type) {
            case 'ticket_trends':
                return $this->getTicketTrendsData($period, $startDate, $endDate);
            case 'technician_performance':
                return $this->getTechnicianPerformanceData($period, $startDate, $endDate);
            case 'category_distribution':
                return $this->getCategoryDistributionData($period, $startDate, $endDate);
            case 'resolution_time':
                return $this->getResolutionTimeData($period, $startDate, $endDate);
            case 'priority_distribution':
                return $this->getPriorityDistributionData($period, $startDate, $endDate);
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    private function getDashboardData()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total_tickets' => Tickets::count(),
            'open_tickets' => Tickets::open()->count(),
            'closed_tickets' => Tickets::closed()->count(),
            'overdue_tickets' => Tickets::where('due_date', '<', now())->whereIn('status', ['open', 'in_progress', 'assigned', 'pending'])->count(),
            'tickets_this_month' => Tickets::where('created_at', '>=', $currentMonth)->count(),
            'tickets_last_month' => Tickets::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
            'avg_resolution_time' => $this->getAverageResolutionTime(),
            'top_categories' => $this->getTopCategories(),
            'technician_stats' => $this->getTechnicianStats(),
            'recent_activities' => $this->getRecentActivities(),
        ];
    }

    private function getTicketTrendsData($period, $startDate = null, $endDate = null)
    {
        $query = Tickets::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status IN ("closed", "resolved") THEN 1 ELSE 0 END) as resolved')
        );

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $days = $period === 'month' ? 30 : 7;
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $data = $query->groupBy('date')
                     ->orderBy('date')
                     ->get();

        return response()->json([
            'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d')),
            'datasets' => [
                [
                    'label' => 'Total Tickets',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 2
                ],
                [
                    'label' => 'Resolved Tickets',
                    'data' => $data->pluck('resolved'),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'borderWidth' => 2
                ]
            ]
        ]);
    }

    private function getTechnicianPerformanceData($period, $startDate = null, $endDate = null)
    {
        $query = User::role('technician')
                    ->with(['assignedTickets' => function($q) use ($startDate, $endDate) {
                        if ($startDate && $endDate) {
                            $q->whereBetween('created_at', [$startDate, $endDate]);
                        } else {
                            $days = $period === 'month' ? 30 : 7;
                            $q->where('created_at', '>=', Carbon::now()->subDays($days));
                        }
                    }]);

        $technicians = $query->get()->map(function ($user) {
            $tickets = $user->assignedTickets;
            $totalAssigned = $tickets->count();
            $resolved = $tickets->whereIn('status', ['resolved', 'closed'])->count();
            $inProgress = $tickets->where('status', 'in_progress')->count();
            
            // Calculate average resolution time (in hours)
            $resolvedTickets = $tickets->whereIn('status', ['resolved', 'closed'])->whereNotNull('resolved_at');
            $avgResolutionTime = 0;
            
            if ($resolvedTickets->count() > 0) {
                $totalResolutionTime = $resolvedTickets->sum(function($ticket) {
                    return $ticket->resolved_at ? 
                        Carbon::parse($ticket->created_at)->diffInHours(Carbon::parse($ticket->resolved_at)) : 0;
                });
                $avgResolutionTime = $totalResolutionTime / $resolvedTickets->count();
            }
            
            // Calculate efficiency rating (percentage of resolved vs assigned)
            $efficiencyRating = $totalAssigned > 0 ? ($resolved / $totalAssigned) * 100 : 0;
            
            return [
                'name' => $user->name,
                'total_assigned' => $totalAssigned,
                'resolved' => $resolved,
                'in_progress' => $inProgress,
                'avg_resolution_time' => round($avgResolutionTime, 1), // in hours
                'efficiency_rating' => round($efficiencyRating, 1), // as percentage
            ];
        });

        return response()->json([
            'labels' => $technicians->pluck('name'),
            'datasets' => [
                [
                    'label' => 'Total Assigned',
                    'data' => $technicians->pluck('total_assigned'),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Resolved',
                    'data' => $technicians->pluck('resolved'),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'In Progress',
                    'data' => $technicians->pluck('in_progress'),
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'borderColor' => 'rgb(255, 206, 86)',
                    'borderWidth' => 1
                ]
            ],
            // Additional data for tooltips and metrics display
            'technician_metrics' => $technicians->map(function($tech) {
                return [
                    'name' => $tech['name'],
                    'efficiency' => $tech['efficiency_rating'],
                    'avg_resolution' => $tech['avg_resolution_time']
                ];
            })
        ]);
    }

    private function getCategoryDistributionData($period, $startDate = null, $endDate = null)
    {
        $query = TicketCategory::withCount(['tickets' => function($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $days = $period === 'month' ? 30 : 7;
                $q->where('created_at', '>=', Carbon::now()->subDays($days));
            }
        }]);

        $categories = $query->get();

        return response()->json([
            'labels' => $categories->pluck('name'),
            'datasets' => [
                [
                    'data' => $categories->pluck('tickets_count'),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ],
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    private function getResolutionTimeData($period, $startDate = null, $endDate = null)
    {
        $query = Tickets::whereNotNull('resolved_at')
                       ->whereNotNull('response_time_minutes');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $days = $period === 'month' ? 30 : 7;
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $data = $query->selectRaw('
            AVG(CASE WHEN priority = "low" THEN response_time_minutes END) as low_priority,
            AVG(CASE WHEN priority = "medium" THEN response_time_minutes END) as medium_priority,
            AVG(CASE WHEN priority = "high" THEN response_time_minutes END) as high_priority,
            AVG(CASE WHEN priority = "critical" THEN response_time_minutes END) as critical_priority,
            AVG(CASE WHEN priority = "urgent" THEN response_time_minutes END) as urgent_priority
        ')->first();

        return response()->json([
            'labels' => ['Low', 'Medium', 'High', 'Critical', 'Urgent'],
            'datasets' => [
                [
                    'label' => 'Average Resolution Time (minutes)',
                    'data' => [
                        round($data->low_priority ?: 0),
                        round($data->medium_priority ?: 0),
                        round($data->high_priority ?: 0),
                        round($data->critical_priority ?: 0),
                        round($data->urgent_priority ?: 0),
                    ],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                    ],
                    'borderColor' => [
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(255, 99, 132)',
                        'rgb(153, 102, 255)',
                    ],
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    private function getPriorityDistributionData($period, $startDate = null, $endDate = null)
    {
        $query = Tickets::select('priority', DB::raw('COUNT(*) as count'));

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $days = $period === 'month' ? 30 : 7;
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $data = $query->groupBy('priority')->get();

        return response()->json([
            'labels' => $data->pluck('priority')->map(fn($p) => ucfirst($p)),
            'datasets' => [
                [
                    'data' => $data->pluck('count'),
                    'backgroundColor' => [
                        '#4BC0C0', // low
                        '#36A2EB', // medium
                        '#FFCE56', // high
                        '#FF6384', // critical
                        '#9966FF', // urgent
                    ],
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    private function getAverageResolutionTime()
    {
        return Tickets::whereNotNull('resolved_at')
                     ->whereNotNull('response_time_minutes')
                     ->avg('response_time_minutes') ?: 0;
    }

    private function getTopCategories()
    {
        return TicketCategory::withCount('tickets')
                            ->orderBy('tickets_count', 'desc')
                            ->take(5)
                            ->get();
    }

    private function getTechnicianStats()
    {
        return User::role('technician')
                  ->withCount(['assignedTickets as total_assigned',
                              'assignedTickets as resolved' => function($q) {
                                  $q->where('status', 'resolved');
                              }])
                  ->get();
    }

    private function getRecentActivities()
    {
        return Tickets::with(['user', 'assignedTo'])
                     ->orderBy('last_activity_at', 'desc')
                     ->take(10)
                     ->get();
    }
}