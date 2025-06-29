<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tickets;
use App\Models\User;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:generate-reports']);
    }

    public function exportManagerDashboard(Request $request)
    {
        $period = $request->get('period', 'week');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $data = $this->getReportData($period, $startDate, $endDate);
        
        $pdf = Pdf::loadView('admin.reports.manager-dashboard', [
            'data' => $data,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
        ]);

        $filename = 'manager-dashboard-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportTicketReport(Request $request)
    {
        $filters = $request->only(['status', 'priority', 'category_id', 'assigned_to', 'created_from', 'created_to']);
        
        $query = Tickets::with(['user', 'assignedTo', 'category', 'subcategory']);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        
        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }
        
        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('admin.reports.ticket-report', [
            'tickets' => $tickets,
            'filters' => $filters,
            'generatedAt' => now(),
        ]);

        $filename = 'ticket-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportTechnicianPerformance(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        $technicians = User::role('technician')
            ->with(['assignedTickets' => function($q) use ($dateRange) {
                $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            }])
            ->get()
            ->map(function ($user) {
                $tickets = $user->assignedTickets;
                $resolvedTickets = $tickets->where('status', 'resolved');
                
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_assigned' => $tickets->count(),
                    'resolved' => $resolvedTickets->count(),
                    'in_progress' => $tickets->where('status', 'in_progress')->count(),
                    'pending' => $tickets->where('status', 'pending')->count(),
                    'avg_resolution_time' => $resolvedTickets->avg('response_time_minutes') ?: 0,
                    'resolution_rate' => $tickets->count() > 0 ? ($resolvedTickets->count() / $tickets->count()) * 100 : 0,
                ];
            });

        $pdf = Pdf::loadView('admin.reports.technician-performance', [
            'technicians' => $technicians,
            'period' => $period,
            'dateRange' => $dateRange,
            'generatedAt' => now(),
        ]);

        $filename = 'technician-performance-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function getReportData($period, $startDate = null, $endDate = null)
    {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        return [
            'summary' => $this->getSummaryData($dateRange),
            'ticket_trends' => $this->getTicketTrendsData($dateRange),
            'category_stats' => $this->getCategoryStats($dateRange),
            'technician_stats' => $this->getTechnicianStats($dateRange),
            'priority_distribution' => $this->getPriorityDistribution($dateRange),
            'resolution_times' => $this->getResolutionTimes($dateRange),
        ];
    }

    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
            ];
        }

        $end = Carbon::now()->endOfDay();
        
        switch ($period) {
            case 'week':
                $start = Carbon::now()->subWeek()->startOfDay();
                break;
            case 'month':
                $start = Carbon::now()->subMonth()->startOfDay();
                break;
            case 'quarter':
                $start = Carbon::now()->subQuarter()->startOfDay();
                break;
            case 'year':
                $start = Carbon::now()->subYear()->startOfDay();
                break;
            default:
                $start = Carbon::now()->subWeek()->startOfDay();
        }

        return ['start' => $start, 'end' => $end];
    }

    private function getSummaryData($dateRange)
    {
        $baseQuery = Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        return [
            'total_tickets' => $baseQuery->count(),
            'open_tickets' => $baseQuery->clone()->open()->count(),
            'closed_tickets' => $baseQuery->clone()->closed()->count(),
            'overdue_tickets' => $baseQuery->clone()->overdue()->count(),
            'avg_resolution_time' => $baseQuery->clone()
                ->whereNotNull('resolved_at')
                ->whereNotNull('response_time_minutes')
                ->avg('response_time_minutes') ?: 0,
        ];
    }

    private function getTicketTrendsData($dateRange)
    {
        return Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status IN ("closed", "resolved") THEN 1 ELSE 0 END) as resolved')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getCategoryStats($dateRange)
    {
        return TicketCategory::withCount(['tickets' => function($q) use ($dateRange) {
            $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }])
        ->orderBy('tickets_count', 'desc')
        ->get();
    }

    private function getTechnicianStats($dateRange)
    {
        return User::role('technician')
            ->withCount([
                'assignedTickets as total_assigned' => function($q) use ($dateRange) {
                    $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                },
                'assignedTickets as resolved' => function($q) use ($dateRange) {
                    $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                      ->where('status', 'resolved');
                }
            ])
            ->get();
    }

    private function getPriorityDistribution($dateRange)
    {
        return Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->get();
    }

    private function getResolutionTimes($dateRange)
    {
        return Tickets::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('resolved_at')
            ->whereNotNull('response_time_minutes')
            ->selectRaw('
                priority,
                AVG(response_time_minutes) as avg_resolution_time,
                MIN(response_time_minutes) as min_resolution_time,
                MAX(response_time_minutes) as max_resolution_time
            ')
            ->groupBy('priority')
            ->get();
    }
}