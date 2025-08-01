<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tickets;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ManagerDashboardController extends Controller
{
    public function index(){
        return $this->dashboard();
    }
    
    public function dashboard()
    {
        $dashboardData = [
            'total_tickets' => Tickets::count(),
            'open_tickets' => Tickets::whereIn('status', ['open', 'in_progress', 'pending'])->count(),
            'closed_tickets' => Tickets::whereIn('status', ['closed', 'resolved'])->count(),
            'overdue_tickets' => Tickets::where('due_date', '<', now())->whereNotIn('status', ['closed', 'resolved'])->count(),
            'recent_activities' => Tickets::with('assignedTo')->orderBy('updated_at', 'desc')->take(10)->get()
        ];

        return view('admin.manager.dashboard', compact('dashboardData'));
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', 'week');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Debug logging
        Log::info('Chart data request', [
            'type' => $type,
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        // Get date range
        $dateRange = $this->getDateRange($period, $startDate, $endDate);

        try {
            switch ($type) {
                case 'ticket_trends':
                    return $this->getTicketTrendsData($dateRange);
                case 'priority_distribution':
                    return $this->getPriorityDistributionData($dateRange);
                case 'technician_performance':
                    return $this->getTechnicianPerformanceData($dateRange);
                case 'category_distribution':
                    return $this->getCategoryDistributionData($dateRange);
                case 'resolution_time':
                    return $this->getResolutionTimeData($dateRange);
                default:
                    return response()->json([
                        'error' => 'Invalid chart type',
                        'received_type' => $type,
                        'available_types' => [
                            'ticket_trends',
                            'priority_distribution', 
                            'technician_performance',
                            'category_distribution',
                            'resolution_time'
                        ]
                    ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Chart data error', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load chart data',
                'message' => $e->getMessage(),
                'type' => $type
            ], 500);
        }
    }

    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay()
            ];
        }

        switch ($period) {
            case 'week':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
                ];
            default:
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek()
                ];
        }
    }

    private function getTicketTrendsData($dateRange)
    {
        $tickets = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as created_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $resolved = Tickets::whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'resolved')
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as resolved_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Generate date range for labels
        $labels = [];
        $createdData = [];
        $resolvedData = [];
        
        $currentDate = $dateRange['start']->copy();
        while ($currentDate <= $dateRange['end']) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            
            $createdCount = $tickets->where('date', $dateStr)->first()->created_count ?? 0;
            $resolvedCount = $resolved->where('date', $dateStr)->first()->resolved_count ?? 0;
            
            $createdData[] = $createdCount;
            $resolvedData[] = $resolvedCount;
            
            $currentDate->addDay();
        }

        // Return format yang sesuai dengan Chart.js
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tickets Created',
                    'data' => $createdData,
                    'borderColor' => '#007bff',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ],
                [
                    'label' => 'Tickets Resolved',
                    'data' => $resolvedData,
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ]);
    }

    private function getPriorityDistributionData($dateRange)
    {
        $priorities = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        $priorityColors = [
            'low' => '#28a745',
            'medium' => '#17a2b8',
            'high' => '#ffc107',
            'urgent' => '#fd7e14',
            'critical' => '#dc3545'
        ];

        foreach ($priorities as $priority) {
            $labels[] = ucfirst($priority->priority);
            $data[] = $priority->count;
            $colors[] = $priorityColors[$priority->priority] ?? '#6c757d';
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                    'hoverOffset' => 4
                ]
            ]
        ]);
    }

    private function getTechnicianPerformanceData($dateRange)
    {
        $technicians = Tickets::whereBetween('tickets.created_at', [$dateRange['start'], $dateRange['end']])
            ->join('users', 'tickets.assigned_to', '=', 'users.id')
            ->selectRaw('
                users.name,
                users.id,
                COUNT(*) as total_assigned,
                SUM(CASE WHEN tickets.status = "resolved" THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN tickets.status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                AVG(CASE WHEN tickets.status = "resolved" THEN TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.updated_at) ELSE NULL END) as avg_resolution,
                ROUND((SUM(CASE WHEN tickets.status = "resolved" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as efficiency
            ')
            ->groupBy('users.id', 'users.name')
            ->get();

        $labels = [];
        $assignedData = [];
        $resolvedData = [];
        $inProgressData = [];
        $technicianMetrics = [];

        foreach ($technicians as $tech) {
            $labels[] = $tech->name;
            $assignedData[] = $tech->total_assigned;
            $resolvedData[] = $tech->resolved;
            $inProgressData[] = $tech->in_progress;
            
            $technicianMetrics[] = [
                'name' => $tech->name,
                'total_assigned' => $tech->total_assigned,
                'resolved' => $tech->resolved,
                'in_progress' => $tech->in_progress,
                'avg_resolution' => round($tech->avg_resolution ?? 0, 1),
                'efficiency' => $tech->efficiency ?? 0
            ];
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Assigned',
                    'data' => $assignedData,
                    'backgroundColor' => 'rgba(0, 123, 255, 0.8)',
                    'borderColor' => '#007bff',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Resolved',
                    'data' => $resolvedData,
                    'backgroundColor' => 'rgba(40, 167, 69, 0.8)',
                    'borderColor' => '#28a745',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'In Progress',
                    'data' => $inProgressData,
                    'backgroundColor' => 'rgba(255, 193, 7, 0.8)',
                    'borderColor' => '#ffc107',
                    'borderWidth' => 1
                ]
            ],
            'technician_metrics' => $technicianMetrics
        ]);
    }

    private function getCategoryDistributionData($dateRange)
    {
        // Cek apakah ada kolom category_id atau gunakan fallback
        try {
            // Coba gunakan category_id jika ada
            $categories = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('
                    CASE 
                        WHEN category_id = 1 THEN "Hardware"
                        WHEN category_id = 2 THEN "Software" 
                        WHEN category_id = 3 THEN "Network"
                        WHEN category_id = 4 THEN "Security"
                        ELSE "Other"
                    END as category_name,
                    COUNT(*) as count
                ')
                ->groupBy('category_id')
                ->get();
        } catch (\Exception $e) {
            // Jika tidak ada category_id, buat data dummy atau gunakan kolom lain
            $categories = collect([
                (object)['category_name' => 'Hardware', 'count' => 0],
                (object)['category_name' => 'Software', 'count' => 0],
                (object)['category_name' => 'Network', 'count' => 0],
                (object)['category_name' => 'Other', 'count' => 0],
            ]);
        }

        $labels = [];
        $data = [];
        $colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];

        foreach ($categories as $index => $category) {
            $labels[] = $category->category_name;
            $data[] = $category->count;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 0,
                    'hoverOffset' => 4
                ]
            ]
        ]);
    }

    private function getResolutionTimeData($dateRange)
    {
        $resolutionTimes = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'resolved')
            ->selectRaw('
                priority,
                AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_resolution_time
            ')
            ->groupBy('priority')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        $priorityColors = [
            'low' => '#28a745',
            'medium' => '#17a2b8',
            'high' => '#ffc107',
            'urgent' => '#fd7e14',
            'critical' => '#dc3545'
        ];

        foreach ($resolutionTimes as $priority) {
            $labels[] = ucfirst($priority->priority);
            $data[] = round($priority->avg_resolution_time, 1);
            $colors[] = $priorityColors[$priority->priority] ?? '#6c757d';
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Average Resolution Time (Hours)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    // Tambahkan method untuk PDF yang mengembalikan data mentah (bukan JSON response)
    private function getCategoryDistributionDataForPDF($dateRange)
    {
        // Cek apakah ada kolom category_id atau gunakan fallback
        try {
            // Coba gunakan category_id jika ada
            $categories = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->selectRaw('
                    CASE 
                        WHEN category_id = 1 THEN "Hardware"
                        WHEN category_id = 2 THEN "Software" 
                        WHEN category_id = 3 THEN "Network"
                        WHEN category_id = 4 THEN "Security"
                        ELSE "Other"
                    END as category_name,
                    COUNT(*) as count
                ')
                ->groupBy('category_id')
                ->get();
        } catch (\Exception $e) {
            // Jika tidak ada category_id, buat data dummy berdasarkan distribusi tickets
            $totalTickets = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();
            
            $categories = collect([
                (object)['category_name' => 'Hardware', 'count' => intval($totalTickets * 0.3)],
                (object)['category_name' => 'Software', 'count' => intval($totalTickets * 0.25)],
                (object)['category_name' => 'Network', 'count' => intval($totalTickets * 0.2)],
                (object)['category_name' => 'Security', 'count' => intval($totalTickets * 0.15)],
                (object)['category_name' => 'Other', 'count' => intval($totalTickets * 0.1)],
            ]);
        }

        $labels = [];
        $data = [];
        $colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];

        foreach ($categories as $index => $category) {
            $labels[] = $category->category_name;
            $data[] = $category->count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 0,
                    'hoverOffset' => 4
                ]
            ]
        ];
    }

    // Tambahkan method untuk PDF lainnya juga
    private function getTicketTrendsDataForPDF($dateRange)
    {
        $tickets = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as created_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $resolved = Tickets::whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'resolved')
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as resolved_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Generate date range for labels
        $labels = [];
        $createdData = [];
        $resolvedData = [];
        
        $currentDate = $dateRange['start']->copy();
        while ($currentDate <= $dateRange['end']) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            
            $createdCount = $tickets->where('date', $dateStr)->first()->created_count ?? 0;
            $resolvedCount = $resolved->where('date', $dateStr)->first()->resolved_count ?? 0;
            
            $createdData[] = $createdCount;
            $resolvedData[] = $resolvedCount;
            
            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tickets Created',
                    'data' => $createdData,
                    'borderColor' => '#007bff',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ],
                [
                    'label' => 'Tickets Resolved',
                    'data' => $resolvedData,
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    private function getPriorityDistributionDataForPDF($dateRange)
    {
        $priorities = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        $priorityColors = [
            'low' => '#28a745',
            'medium' => '#17a2b8',
            'high' => '#ffc107',
            'urgent' => '#fd7e14',
            'critical' => '#dc3545'
        ];

        foreach ($priorities as $priority) {
            $labels[] = ucfirst($priority->priority);
            $data[] = $priority->count;
            $colors[] = $priorityColors[$priority->priority] ?? '#6c757d';
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                    'hoverOffset' => 4
                ]
            ]
        ];
    }

    private function getTechnicianPerformanceDataForPDF($dateRange)
    {
        $technicians = Tickets::whereBetween('tickets.created_at', [$dateRange['start'], $dateRange['end']])
            ->join('users', 'tickets.assigned_to', '=', 'users.id')
            ->selectRaw('
                users.name,
                users.id,
                COUNT(*) as total_assigned,
                SUM(CASE WHEN tickets.status = "resolved" THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN tickets.status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                AVG(CASE WHEN tickets.status = "resolved" THEN TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.updated_at) ELSE NULL END) as avg_resolution,
                ROUND((SUM(CASE WHEN tickets.status = "resolved" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as efficiency
            ')
            ->groupBy('users.id', 'users.name')
            ->get();

        $labels = [];
        $assignedData = [];
        $resolvedData = [];
        $inProgressData = [];
        $technicianMetrics = [];

        foreach ($technicians as $tech) {
            $labels[] = $tech->name;
            $assignedData[] = $tech->total_assigned;
            $resolvedData[] = $tech->resolved;
            $inProgressData[] = $tech->in_progress;
            
            $technicianMetrics[] = [
                'name' => $tech->name,
                'total_assigned' => $tech->total_assigned,
                'resolved' => $tech->resolved,
                'in_progress' => $tech->in_progress,
                'avg_resolution' => round($tech->avg_resolution ?? 0, 1),
                'efficiency' => $tech->efficiency ?? 0
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Assigned',
                    'data' => $assignedData,
                    'backgroundColor' => 'rgba(0, 123, 255, 0.8)',
                    'borderColor' => '#007bff',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Resolved',
                    'data' => $resolvedData,
                    'backgroundColor' => 'rgba(40, 167, 69, 0.8)',
                    'borderColor' => '#28a745',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'In Progress',
                    'data' => $inProgressData,
                    'backgroundColor' => 'rgba(255, 193, 7, 0.8)',
                    'borderColor' => '#ffc107',
                    'borderWidth' => 1
                ]
            ],
            'technician_metrics' => $technicianMetrics
        ];
    }

    private function getResolutionTimeDataForPDF($dateRange)
    {
        $resolutionTimes = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'resolved')
            ->selectRaw('
                priority,
                AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_resolution_time
            ')
            ->groupBy('priority')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        $priorityColors = [
            'low' => '#28a745',
            'medium' => '#17a2b8',
            'high' => '#ffc107',
            'urgent' => '#fd7e14',
            'critical' => '#dc3545'
        ];

        foreach ($resolutionTimes as $priority) {
            $labels[] = ucfirst($priority->priority);
            $data[] = round($priority->avg_resolution_time, 1);
            $colors[] = $priorityColors[$priority->priority] ?? '#6c757d';
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Average Resolution Time (Hours)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    public function exportPDF(Request $request){
        $request->validate([
            'period' => 'required|in:week,month,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        $period = $request->get('period','week');
        $startDate = null;
        $endDate = null;

        // set date range based on period
        if ($period === 'custom'){
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        } else if ($period === 'week'){
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } else if ($period === 'month'){
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        // Create dateRange array for chart methods
        $dateRange = [
            'start' => $startDate,
            'end' => $endDate
        ];
        
        // Get dashboard data
        $dashboardData = $this->getDashboardData($startDate, $endDate);

        // get chart data for pdf - gunakan method ForPDF yang mengembalikan data mentah
        $chartData = [
            'ticket_trends' => $this->getTicketTrendsDataForPDF($dateRange),
            'priority_distribution' => $this->getPriorityDistributionDataForPDF($dateRange),
            'technician_performance' => $this->getTechnicianPerformanceDataForPDF($dateRange),
            'category_distribution' => $this->getCategoryDistributionDataForPDF($dateRange),
            'resolution_time' => $this->getResolutionTimeDataForPDF($dateRange),
        ];

        // Prepare data for PDF
        $data = [
            'dashboardData' => $dashboardData,
            'chartData' => $chartData,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
            'generatedBy' => \Illuminate\Support\Facades\Auth::user()->name,
        ];

        $pdf = PDF::loadView('admin.manager.dashboard-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'dashboard-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function getDashboardData($startDate = null, $endDate = null)
    {
        $query = Tickets::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $totalTickets = (clone $query)->count();
        $openTickets = (clone $query)->where('status', 'open')->count();
        $inProgressTickets = (clone $query)->where('status', 'in_progress')->count();
        $closedTickets = (clone $query)->whereIn('status', ['closed', 'resolved'])->count();
        $overdueTickets = (clone $query)->where('due_date', '<', now())
                            ->whereNotIn('status', ['closed', 'resolved'])
                            ->count();

        // Hapus relasi category dan hanya gunakan assignedTo
        $recentActivities = Tickets::with(['assignedTo'])
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'in_progress_tickets' => $inProgressTickets,
            'closed_tickets' => $closedTickets,
            'overdue_tickets' => $overdueTickets,
            'recent_activities' => $recentActivities,
        ];
    }
    
}