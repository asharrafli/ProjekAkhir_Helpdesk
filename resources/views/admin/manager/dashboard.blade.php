@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">Executive Dashboard</h1>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="periodDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar"></i> This Week
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item period-filter" href="#" data-period="week">This Week</a></li>
                            <li><a class="dropdown-item period-filter" href="#" data-period="month">This Month</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#dateRangeModal">Custom Range</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-success" id="exportBtn">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['total_tickets']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Open Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['open_tickets']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Resolved Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['closed_tickets']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($dashboardData['overdue_tickets']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Ticket Trends</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item refresh-chart" href="#" data-chart="ticket_trends">Refresh</a>
                            <a class="dropdown-item export-chart" href="#" data-chart="ticket_trends">Export PNG</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="ticketTrendsChart" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Priority Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="priorityDistributionChart" width="100%" height="50"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Technician Performance</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="technicianPerformanceChart" width="100%" height="50"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Category Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="categoryDistributionChart" width="100%" height="50"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolution Time Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Average Resolution Time by Priority</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="resolutionTimeChart" width="100%" height="30"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Ticket Activities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Assigned To</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dashboardData['recent_activities'] as $ticket)
                                <tr>
                                    <td>{{ $ticket->ticket_number }}</td>
                                    <td>{{ Str::limit($ticket->title, 30) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $ticket->status_color }}">
                                            {{ ucfirst($ticket->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $ticket->priority_color }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->assignedTo->name ?? 'Unassigned' }}</td>
                                    <td>{{ $ticket->last_activity_at ? $ticket->last_activity_at->diffForHumans() : 'No activity' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dateRangeForm">
                    <div class="row">
                        <div class="col-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyDateRange">Apply</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPeriod = 'week';
    let currentStartDate = null;
    let currentEndDate = null;
    let charts = {};

    // Initialize all charts
    initializeCharts();

    // Period filter handlers
    document.querySelectorAll('.period-filter').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            currentPeriod = this.dataset.period;
            currentStartDate = null;
            currentEndDate = null;
            document.querySelector('#periodDropdown').textContent = this.textContent;
            refreshAllCharts();
        });
    });

    // Date range handlers
    document.getElementById('applyDateRange').addEventListener('click', function() {
        currentStartDate = document.getElementById('start_date').value;
        currentEndDate = document.getElementById('end_date').value;
        currentPeriod = 'custom';
        
        if (currentStartDate && currentEndDate) {
            document.querySelector('#periodDropdown').textContent = 'Custom Range';
            document.querySelector('[data-bs-dismiss="modal"]').click();
            refreshAllCharts();
        }
    });

    // Export handlers
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Generate PDF report
        generatePDFReport();
    });

    function initializeCharts() {
        // Ticket Trends Chart
        loadChart('ticket_trends', 'ticketTrendsChart', 'line');
        
        // Priority Distribution Chart
        loadChart('priority_distribution', 'priorityDistributionChart', 'doughnut');
        
        // Technician Performance Chart
        loadChart('technician_performance', 'technicianPerformanceChart', 'bar');
        
        // Category Distribution Chart
        loadChart('category_distribution', 'categoryDistributionChart', 'pie');
        
        // Resolution Time Chart
        loadChart('resolution_time', 'resolutionTimeChart', 'bar');
    }

    function loadChart(type, canvasId, chartType) {
        const params = new URLSearchParams({
            type: type,
            period: currentPeriod
        });
        
        if (currentStartDate && currentEndDate) {
            params.append('start_date', currentStartDate);
            params.append('end_date', currentEndDate);
        }

        fetch(`{{ route('manager.dashboard.chart-data') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById(canvasId).getContext('2d');
                
                // Destroy existing chart if it exists
                if (charts[canvasId]) {
                    charts[canvasId].destroy();
                }
                
                charts[canvasId] = new Chart(ctx, {
                    type: chartType,
                    data: data,
                    options: getChartOptions(chartType)
                });
            })
            .catch(error => console.error('Error loading chart:', error));
    }

    function getChartOptions(chartType) {
        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: chartType === 'line' || chartType === 'bar' ? 'top' : 'right'
                }
            }
        };

        if (chartType === 'line' || chartType === 'bar') {
            baseOptions.scales = {
                y: {
                    beginAtZero: true
                }
            };
        }

        return baseOptions;
    }

    function refreshAllCharts() {
        Object.keys(charts).forEach(function(chartId) {
            const chartType = getChartTypeFromId(chartId);
            loadChart(chartType, chartId, getChartDisplayType(chartId));
        });
    }

    function getChartTypeFromId(chartId) {
        const mapping = {
            'ticketTrendsChart': 'ticket_trends',
            'priorityDistributionChart': 'priority_distribution',
            'technicianPerformanceChart': 'technician_performance',
            'categoryDistributionChart': 'category_distribution',
            'resolutionTimeChart': 'resolution_time'
        };
        return mapping[chartId];
    }

    function getChartDisplayType(chartId) {
        const mapping = {
            'ticketTrendsChart': 'line',
            'priorityDistributionChart': 'doughnut',
            'technicianPerformanceChart': 'bar',
            'categoryDistributionChart': 'pie',
            'resolutionTimeChart': 'bar'
        };
        return mapping[chartId];
    }

    function generatePDFReport() {
        const params = new URLSearchParams({
            period: currentPeriod
        });
        
        if (currentStartDate && currentEndDate) {
            params.append('start_date', currentStartDate);
            params.append('end_date', currentEndDate);
        }

        window.open(`{{ route('manager.dashboard.export') }}?${params}`, '_blank');
    }

    // Refresh chart handlers
    document.querySelectorAll('.refresh-chart').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const chartType = this.dataset.chart;
            const chartId = getChartIdFromType(chartType);
            const displayType = getChartDisplayType(chartId);
            loadChart(chartType, chartId, displayType);
        });
    });

    function getChartIdFromType(chartType) {
        const mapping = {
            'ticket_trends': 'ticketTrendsChart',
            'priority_distribution': 'priorityDistributionChart',
            'technician_performance': 'technicianPerformanceChart',
            'category_distribution': 'categoryDistributionChart',
            'resolution_time': 'resolutionTimeChart'
        };
        return mapping[chartType];
    }
});
</script>
@endpush

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.chart-area, .chart-pie, .chart-bar {
    position: relative;
    height: 300px;
}

.badge-open, .badge-info { background-color: #17a2b8; }
.badge-in_progress, .badge-warning { background-color: #ffc107; color: #212529; }
.badge-assigned, .badge-primary { background-color: #007bff; }
.badge-pending, .badge-secondary { background-color: #6c757d; }
.badge-escalated, .badge-danger { background-color: #dc3545; }
.badge-closed, .badge-resolved, .badge-success { background-color: #28a745; }

.badge-low { background-color: #28a745; }
.badge-medium { background-color: #17a2b8; }
.badge-high { background-color: #ffc107; color: #212529; }
.badge-critical, .badge-urgent { background-color: #dc3545; }
</style>
@endpush