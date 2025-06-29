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
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-primary text-uppercase fw-bold mb-1">Total Tickets</div>
                        <div class="h4 mb-0 fw-bold text-dark">{{ number_format($dashboardData['total_tickets']) }}</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-ticket-alt fa-2x text-muted opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-warning text-uppercase fw-bold mb-1">Open Tickets</div>
                        <div class="h4 mb-0 fw-bold text-dark">{{ number_format($dashboardData['open_tickets']) }}</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-folder-open fa-2x text-muted opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-success text-uppercase fw-bold mb-1">Resolved Tickets</div>
                        <div class="h4 mb-0 fw-bold text-dark">{{ number_format($dashboardData['closed_tickets']) }}</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle fa-2x text-muted opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-danger text-uppercase fw-bold mb-1">Overdue Tickets</div>
                        <div class="h4 mb-0 fw-bold text-dark">{{ number_format($dashboardData['overdue_tickets']) }}</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-muted opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technician Performance Metrics Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                    <h5 class="mb-0 fw-bold text-primary">Technician Performance Metrics</h5>
                    <button class="btn btn-sm btn-outline-primary" id="refreshTechnicianMetrics">
                        <i class="fas fa-sync-alt"></i> <span class="d-none d-sm-inline">Refresh</span>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="technicianMetricsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Technician</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Resolved</th>
                                    <th class="text-center d-none d-md-table-cell">In Progress</th>
                                    <th class="text-center d-none d-lg-table-cell">Avg Time</th>
                                    <th class="text-center d-none d-xl-table-cell">Efficiency</th>
                                    <th class="text-center pe-3">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="mt-2 text-muted">Loading performance metrics...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                    <h5 class="mb-0 fw-bold text-primary">Ticket Trends</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item refresh-chart" href="#" data-chart="ticket_trends"><i class="fas fa-sync-alt me-2"></i>Refresh</a></li>
                            <li><a class="dropdown-item export-chart" href="#" data-chart="ticket_trends"><i class="fas fa-download me-2"></i>Export PNG</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ticketTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold text-primary">Priority Distribution</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-container-pie">
                        <canvas id="priorityDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold text-primary">Technician Performance</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="technicianPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold text-primary">Category Distribution</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-container-pie">
                        <canvas id="categoryDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolution Time Chart -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold text-primary">Average Resolution Time by Priority</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container-wide">
                        <canvas id="resolutionTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold text-primary">Recent Ticket Activities</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Ticket #</th>
                                    <th>Title</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center d-none d-md-table-cell">Priority</th>
                                    <th class="d-none d-lg-table-cell">Assigned To</th>
                                    <th class="pe-3 d-none d-xl-table-cell">Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dashboardData['recent_activities'] as $ticket)
                                <tr>
                                    <td class="ps-3">
                                        <code class="text-primary">{{ $ticket->ticket_number }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ Str::limit($ticket->title, 40) }}</div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'open' => 'bg-info',
                                                'in_progress' => 'bg-warning',
                                                'closed' => 'bg-success',
                                                'resolved' => 'bg-success',
                                                'pending' => 'bg-secondary'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusColors[$ticket->status] ?? 'bg-secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        @php
                                            $priorityColors = [
                                                'low' => 'bg-success',
                                                'medium' => 'bg-info',
                                                'high' => 'bg-warning',
                                                'urgent' => 'bg-danger',
                                                'critical' => 'bg-danger'
                                            ];
                                        @endphp
                                        <span class="badge {{ $priorityColors[$ticket->priority] ?? 'bg-secondary' }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <div class="d-flex align-items-center">
                                            @if($ticket->assignedTo)
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 24px; height: 24px; font-size: 10px;">
                                                    {{ strtoupper(substr($ticket->assignedTo->name, 0, 1)) }}
                                                </div>
                                                <span class="small">{{ $ticket->assignedTo->name }}</span>
                                            @else
                                                <span class="text-muted small">Unassigned</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="pe-3 d-none d-xl-table-cell">
                                        <small class="text-muted">
                                            {{ $ticket->last_activity_at ? $ticket->last_activity_at->diffForHumans() : 'No activity' }}
                                        </small>
                                    </td>
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

    // Load technician performance metrics table
    function loadTechnicianMetrics() {
        const tbody = document.querySelector('#technicianMetricsTable tbody');
        
        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    Loading performance metrics...
                </td>
            </tr>
        `;
        
        const params = new URLSearchParams({
            type: 'technician_performance',
            period: currentPeriod
        });
        
        if (currentStartDate && currentEndDate) {
            params.append('start_date', currentStartDate);
            params.append('end_date', currentEndDate);
        }
        
        fetch(`{{ route('manager.dashboard.chart-data') }}?${params}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Technician metrics data:', data); // Debug log
                const tbody = document.querySelector('#technicianMetricsTable tbody');
                tbody.innerHTML = '';
                
                if (data.technician_metrics && data.technician_metrics.length > 0) {
                    data.technician_metrics.forEach((tech, index) => {
                        const totalAssigned = (data.datasets && data.datasets[0]) ? (data.datasets[0].data[index] || 0) : 0;
                        const resolved = (data.datasets && data.datasets[1]) ? (data.datasets[1].data[index] || 0) : 0;
                        const inProgress = (data.datasets && data.datasets[2]) ? (data.datasets[2].data[index] || 0) : 0;
                        
                        // Determine performance badge
                        let performanceBadge = '';
                        if (tech.efficiency >= 80) {
                            performanceBadge = '<span class="badge badge-success">Excellent</span>';
                        } else if (tech.efficiency >= 60) {
                            performanceBadge = '<span class="badge badge-primary">Good</span>';
                        } else if (tech.efficiency >= 40) {
                            performanceBadge = '<span class="badge badge-warning">Average</span>';
                        } else {
                            performanceBadge = '<span class="badge badge-danger">Needs Improvement</span>';
                        }
                        
                        const row = `
                            <tr>
                                <td class="ps-3"><strong>${tech.name}</strong></td>
                                <td class="text-center">${totalAssigned}</td>
                                <td class="text-center">${resolved}</td>
                                <td class="text-center d-none d-md-table-cell">${inProgress}</td>
                                <td class="text-center d-none d-lg-table-cell">${tech.avg_resolution}h</td>
                                <td class="d-none d-xl-table-cell">
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${tech.efficiency}%">${tech.efficiency}%</div>
                                    </div>
                                </td>
                                <td class="text-center pe-3">${performanceBadge}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No technician data available for the selected period</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading technician metrics:', error);
                const tbody = document.querySelector('#technicianMetricsTable tbody');
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error loading data: ${error.message}</td></tr>`;
            });
    }

    // Refresh technician metrics button
    document.getElementById('refreshTechnicianMetrics').addEventListener('click', function() {
        loadTechnicianMetrics();
    });

    // Load technician metrics on page load
    loadTechnicianMetrics();
});
</script>
@endpush

@push('styles')
<style>
/* Chart Containers */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.chart-container-pie {
    position: relative;
    height: 250px;
    width: 100%;
    max-width: 300px;
    margin: 0 auto;
}

.chart-container-wide {
    position: relative;
    height: 250px;
    width: 100%;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .chart-container {
        height: 250px;
    }
    
    .chart-container-pie {
        height: 200px;
        max-width: 250px;
    }
    
    .chart-container-wide {
        height: 200px;
    }
}

@media (max-width: 768px) {
    .h4 {
        font-size: 1.25rem;
    }
    
    .small {
        font-size: 0.75rem;
    }
}

/* Card enhancements */
.card {
    border: none;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 1rem 1.25rem;
}

/* Table improvements */
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    border-bottom: 2px solid #dee2e6;
    padding: 0.75rem;
}

.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}

/* Badge styles */
.badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.375em 0.75em;
}

/* Loading state */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Progress bar improvements */
.progress {
    height: 1.25rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.progress-bar {
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.25rem;
}

/* Icon improvements */
.fa-2x {
    font-size: 1.75em !important;
}

/* Custom utility classes */
.opacity-75 {
    opacity: 0.75;
}

.border-4 {
    border-width: 4px !important;
}

/* Dropdown improvements */
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.375rem;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Mobile responsiveness */
@media (max-width: 991.98px) {
    .container-fluid {
        padding: 0.75rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}

@media (max-width: 575.98px) {
    .h3 {
        font-size: 1.5rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>
@endpush