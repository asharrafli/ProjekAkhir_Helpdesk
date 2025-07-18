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
                    <button class="btn btn-info" id="testChartsBtn">
                        <i class="fas fa-vial"></i> Test Charts
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Debug: Log immediately when script loads
console.log('=== DASHBOARD SCRIPT LOADING ===');
console.log('Current URL:', window.location.href);
console.log('Chart.js loaded:', typeof Chart !== 'undefined');

// Test Chart.js loading
if (typeof Chart !== 'undefined') {
    console.log('Chart.js version:', Chart.version);
} else {
    console.error('Chart.js failed to load!');
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CONTENT LOADED ===');
    console.log('Dashboard script loaded');
    console.log('Chart.js available:', typeof Chart !== 'undefined');
    
    // Check if all canvas elements exist immediately
    const canvasIds = ['ticketTrendsChart', 'priorityDistributionChart', 'technicianPerformanceChart', 'categoryDistributionChart', 'resolutionTimeChart'];
    canvasIds.forEach(id => {
        const canvas = document.getElementById(id);
        console.log(`Canvas ${id}:`, canvas ? 'Found' : 'NOT FOUND');
        if (canvas) {
            console.log(`Canvas ${id} context:`, canvas.getContext('2d') ? 'OK' : 'FAILED');
        }
    });
    
    // Check test button
    const testBtn = document.getElementById('testChartsBtn');
    console.log('Test button:', testBtn ? 'Found' : 'NOT FOUND');
    
    let currentPeriod = 'week';
    let currentStartDate = null;
    let currentEndDate = null;
    let charts = {};

    // Initialize charts with delay
    setTimeout(function() {
        console.log('Starting chart initialization...');
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
            return;
        }
        initializeCharts();
    }, 1000);

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
        generatePDFReport();
    });

    // Test charts button
    // const testBtn = document.getElementById('testChartsBtn');
    if (testBtn) {
        console.log('Adding click handler to test button...');
        testBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('=== TEST BUTTON CLICKED ===');
            
            // Tambahkan loading indicator
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Charts...';
            this.disabled = true;
            
            setTimeout(() => {
                try {
                    createAllTestCharts();
                } catch (error) {
                    console.error('Error in createAllTestCharts:', error);
                    alert('Error creating test charts: ' + error.message);
                } finally {
                    // Kembalikan button ke state semula
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            }, 500);
        });
    } else {
        console.error('Test button not found!');
    }

    
    function showNoDataMessage(canvasId) {
        const canvas = document.getElementById(canvasId);
        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Data Available</h6>
                    <p class="small text-muted">No data found for the selected period</p>
                </div>
            </div>
        `;
    }

    function createAllTestCharts() {
        console.log('=== CREATING ALL TEST CHARTS ===');
        
        // Pastikan Chart.js tersedia
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded!');
            alert('Chart.js not loaded! Please refresh the page.');
            return;
        }

        // Daftar chart yang akan dibuat
        const chartsToCreate = [
            {
                id: 'ticketTrendsChart',
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Tickets Created',
                        data: [12, 19, 3, 5, 2, 3, 20],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Tickets Resolved',
                        data: [8, 15, 2, 4, 1, 2, 18],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                }
            },
            {
                id: 'priorityDistributionChart',
                type: 'doughnut',
                data: {
                    labels: ['Low', 'Medium', 'High', 'Urgent'],
                    datasets: [{
                        data: [30, 25, 20, 15],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                }
            },
            {
                id: 'technicianPerformanceChart',
                type: 'bar',
                data: {
                    labels: ['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Brown'],
                    datasets: [{
                        label: 'Total Assigned',
                        data: [15, 12, 8, 10],
                        backgroundColor: 'rgba(0, 123, 255, 0.8)',
                        borderColor: '#007bff',
                        borderWidth: 1
                    }, {
                        label: 'Resolved',
                        data: [12, 10, 6, 8],
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }]
                }
            },
            {
                id: 'categoryDistributionChart',
                type: 'pie',
                data: {
                    labels: ['Hardware', 'Software', 'Network', 'Security', 'Other'],
                    datasets: [{
                        data: [25, 30, 20, 15, 10],
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                }
            },
            {
                id: 'resolutionTimeChart',
                type: 'bar',
                data: {
                    labels: ['Low', 'Medium', 'High', 'Urgent', 'Critical'],
                    datasets: [{
                        label: 'Avg Resolution Time (Hours)',
                        data: [4, 8, 12, 2, 1],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(23, 162, 184, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(108, 117, 125, 0.8)'
                        ],
                        borderColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#dc3545',
                            '#6c757d'
                        ],
                        borderWidth: 1
                    }]
                }
            }
        ];

        let successCount = 0;
        let failedCharts = [];

        // Buat setiap chart
        chartsToCreate.forEach(chartConfig => {
            try {
                const canvas = document.getElementById(chartConfig.id);
                
                if (!canvas) {
                    console.error(`Canvas ${chartConfig.id} not found!`);
                    failedCharts.push(chartConfig.id);
                    return;
                }

                console.log(`Creating chart: ${chartConfig.id}`);
                
                // Cek apakah canvas dalam kondisi baik
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    console.error(`Cannot get 2D context for ${chartConfig.id}`);
                    failedCharts.push(chartConfig.id);
                    return;
                }

                // Hapus chart yang sudah ada
                if (charts[chartConfig.id]) {
                    console.log(`Destroying existing chart: ${chartConfig.id}`);
                    charts[chartConfig.id].destroy();
                }

                // Buat chart baru
                charts[chartConfig.id] = new Chart(ctx, {
                    type: chartConfig.type,
                    data: chartConfig.data,
                    options: getChartOptions(chartConfig.type, chartConfig.id)
                });

                console.log(`✓ Chart ${chartConfig.id} created successfully`);
                successCount++;

            } catch (error) {
                console.error(`✗ Error creating chart ${chartConfig.id}:`, error);
                failedCharts.push(chartConfig.id);
                
                // Tampilkan pesan error di canvas
                showChartError(chartConfig.id, error.message);
            }
        });

        // Tampilkan hasil
        if (successCount === chartsToCreate.length) {
            alert(`✅ All ${successCount} test charts created successfully!`);
        } else {
            alert(`⚠️ ${successCount}/${chartsToCreate.length} charts created successfully.\nFailed charts: ${failedCharts.join(', ')}`);
        }
    }

    function showChartError(canvasId, errorMessage) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h6 class="text-muted">Error Loading Chart</h6>
                    <p class="small text-muted">${errorMessage}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Reload Page
                    </button>
                </div>
            </div>
        `;
    }

    function initializeCharts() {
        console.log('Initializing charts...');
        
        // Check if all canvas elements exist
        const canvasIds = ['ticketTrendsChart', 'priorityDistributionChart', 'technicianPerformanceChart', 'categoryDistributionChart', 'resolutionTimeChart'];
        canvasIds.forEach(id => {
            const canvas = document.getElementById(id);
            console.log(`Canvas ${id}:`, canvas ? 'Found' : 'NOT FOUND');
        });
        
        // Load all charts
        loadChart('ticket_trends', 'ticketTrendsChart', 'line');
        loadChart('priority_distribution', 'priorityDistributionChart', 'doughnut');
        loadChart('technician_performance', 'technicianPerformanceChart', 'bar');
        loadChart('category_distribution', 'categoryDistributionChart', 'pie');
        loadChart('resolution_time', 'resolutionTimeChart', 'bar');
    }

    function loadChart(type, canvasId, chartType) {
        console.log(`Loading chart: ${type} on canvas: ${canvasId}`);
        
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`Canvas ${canvasId} not found`);
            return;
        }
        
        const params = new URLSearchParams({
            type: type,
            period: currentPeriod
        });
        
        if (currentStartDate && currentEndDate) {
            params.append('start_date', currentStartDate);
            params.append('end_date', currentEndDate);
        }

        const url = `{{ route('manager.dashboard.chart-data') }}?${params}`;
        console.log(`Fetching from URL: ${url}`);

        fetch(url)
            .then(response => {
                console.log(`Response for ${type}:`, response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log(`Data for ${type}:`, data);
                
                // Validate data format
                if (!data.labels || !data.datasets) {
                    console.error(`Invalid data format for ${type}:`, data);
                    showNoDataMessage(canvasId);
                    return;
                }
                
                if (data.labels.length === 0 || data.datasets.length === 0) {
                    console.warn(`No data available for ${type}`);
                    showNoDataMessage(canvasId);
                    return;
                }
                
                const ctx = canvas.getContext('2d');
                
                // Destroy existing chart if it exists
                if (charts[canvasId]) {
                    charts[canvasId].destroy();
                }
                
                // Create new chart
                charts[canvasId] = new Chart(ctx, {
                    type: chartType,
                    data: data,
                    options: getChartOptions(chartType, type)
                });
                
                console.log(`Chart ${type} created successfully`);
            })
            .catch(error => {
                console.error(`Error loading chart ${type}:`, error);
                // Fallback: create chart with dummy data
                createFallbackChart(canvasId, chartType, type);
            });
    }

    function createFallbackChart(canvasId, chartType, type) {
        console.log(`Creating fallback chart for ${type}`);
        
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        // Dummy data based on chart type
        let fallbackData;
        
        if (chartType === 'line') {
            fallbackData = {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sample Data',
                    data: [12, 19, 3, 5, 2, 3, 7],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true
                }]
            };
        } else if (chartType === 'bar') {
            fallbackData = {
                labels: ['Tech 1', 'Tech 2', 'Tech 3', 'Tech 4'],
                datasets: [{
                    label: 'Total Assigned',
                    data: [10, 15, 8, 12],
                    backgroundColor: 'rgba(0, 123, 255, 0.8)'
                }, {
                    label: 'Resolved',
                    data: [8, 12, 6, 10],
                    backgroundColor: 'rgba(40, 167, 69, 0.8)'
                }]
            };
        } else if (chartType === 'doughnut' || chartType === 'pie') {
            fallbackData = {
                labels: ['Low', 'Medium', 'High', 'Urgent'],
                datasets: [{
                    data: [30, 25, 20, 15],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
                }]
            };
        }
        
        try {
            // Destroy existing chart if it exists
            if (charts[canvasId]) {
                charts[canvasId].destroy();
            }
            
            // Create fallback chart
            charts[canvasId] = new Chart(ctx, {
                type: chartType,
                data: fallbackData,
                options: getChartOptions(chartType, type)
            });
            
            console.log(`Fallback chart created for ${type}`);
        } catch (error) {
            console.error(`Error creating fallback chart for ${type}:`, error);
            showChartError(canvasId, `Failed to create chart: ${error.message}`);
        }
    }

    function getChartOptions(chartType, dataType) {
        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: chartType === 'line' || chartType === 'bar' ? 'top' : 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    cornerRadius: 8
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        };

        // Add scales for line and bar charts
        if (chartType === 'line') {
            baseOptions.scales = {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            };
        } else if (chartType === 'bar') {
            baseOptions.scales = {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            };
        }

        // Special options for resolution time chart
        if (dataType === 'resolution_time' && chartType === 'bar') {
            baseOptions.scales.y.title = {
                display: true,
                text: 'Hours'
            };
        }

        return baseOptions;
    }

    function showChartError(canvasId, errorMessage) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h6 class="text-muted">Error Loading Chart</h6>
                    <p class="small text-muted">${errorMessage}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Reload Page
                    </button>
                </div>
            </div>
        `;
    }

    function showNoDataMessage(canvasId) {
        const canvas = document.getElementById(canvasId);
        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Data Available</h6>
                    <p class="small text-muted">No data found for the selected period</p>
                </div>
            </div>
        `;
    }

    function refreshAllCharts() {
        const chartConfigs = {
            'ticketTrendsChart': { type: 'ticket_trends', chartType: 'line' },
            'priorityDistributionChart': { type: 'priority_distribution', chartType: 'doughnut' },
            'technicianPerformanceChart': { type: 'technician_performance', chartType: 'bar' },
            'categoryDistributionChart': { type: 'category_distribution', chartType: 'pie' },
            'resolutionTimeChart': { type: 'resolution_time', chartType: 'bar' }
        };

        Object.keys(chartConfigs).forEach(function(chartId) {
            const config = chartConfigs[chartId];
            loadChart(config.type, chartId, config.chartType);
        });
        
        // Also refresh technician metrics table
        loadTechnicianMetrics();
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

        // For now, just show alert. You can implement PDF generation later
        alert('PDF export functionality will be implemented');
    }

    // Load technician performance metrics table
    function loadTechnicianMetrics() {
        const tbody = document.querySelector('#technicianMetricsTable tbody');
        
        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted">Loading performance metrics...</div>
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
                const tbody = document.querySelector('#technicianMetricsTable tbody');
                tbody.innerHTML = '';
                
                if (data.technician_metrics && data.technician_metrics.length > 0) {
                    data.technician_metrics.forEach((tech, index) => {
                        const totalAssigned = tech.total_assigned || 0;
                        const resolved = tech.resolved || 0;
                        const inProgress = tech.in_progress || 0;
                        const avgResolution = tech.avg_resolution || 0;
                        const efficiency = tech.efficiency || 0;
                        
                        // Determine performance badge
                        let performanceBadge = '';
                        let badgeClass = '';
                        
                        if (efficiency >= 80) {
                            performanceBadge = 'Excellent';
                            badgeClass = 'bg-success';
                        } else if (efficiency >= 60) {
                            performanceBadge = 'Good';
                            badgeClass = 'bg-primary';
                        } else if (efficiency >= 40) {
                            performanceBadge = 'Average';
                            badgeClass = 'bg-warning';
                        } else {
                            performanceBadge = 'Needs Improvement';
                            badgeClass = 'bg-danger';
                        }
                        
                        const row = `
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 32px; height: 32px; font-size: 12px;">
                                            ${tech.name.charAt(0).toUpperCase()}
                                        </div>
                                        <strong>${tech.name}</strong>
                                    </div>
                                </td>
                                <td class="text-center"><span class="badge bg-light text-dark">${totalAssigned}</span></td>
                                <td class="text-center"><span class="badge bg-success">${resolved}</span></td>
                                <td class="text-center d-none d-md-table-cell"><span class="badge bg-warning">${inProgress}</span></td>
                                <td class="text-center d-none d-lg-table-cell">${avgResolution}h</td>
                                <td class="d-none d-xl-table-cell">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar ${badgeClass}" role="progressbar" style="width: ${efficiency}%">
                                            ${efficiency}%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center pe-3">
                                    <span class="badge ${badgeClass}">${performanceBadge}</span>
                                </td>
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
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Error loading data: ${error.message}</td></tr>`;
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
    height: 350px;
    width: 100%;
    min-height: 300px;
}

.chart-container-pie {
    position: relative;
    height: 280px;
    width: 100%;
    max-width: 350px;
    margin: 0 auto;
}

.chart-container-wide {
    position: relative;
    height: 300px;
    width: 100%;
    min-height: 250px;
}

/* Ensure canvas elements are visible */
canvas {
    display: block !important;
    max-width: 100% !important;
    height: auto !important;
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

/* Icon size adjustments */
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