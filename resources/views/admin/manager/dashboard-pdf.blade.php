{{-- filepath: c:\Users\Administrator\Herd\projekakhir-final\resources\views\admin\manager\dashboard-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header .period {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .header .generated {
            font-size: 10px;
            color: #999;
        }
        
        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .kpi-row {
            display: table-row;
        }
        
        .kpi-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            margin: 5px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            text-align: center;
        }
        
        .kpi-card.warning { border-left-color: #ffc107; }
        .kpi-card.success { border-left-color: #28a745; }
        .kpi-card.danger { border-left-color: #dc3545; }
        
        .kpi-card .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .kpi-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .chart-data {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        .data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; }
        .badge-info { background: #17a2b8; }
        .badge-secondary { background: #6c757d; }
        
        .two-column {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
            vertical-align: top;
        }
        
        .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-3 { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="footer">
        Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }} by {{ $generatedBy }}
    </div>

    <!-- Header -->
    <div class="header">
        <h1>Executive Dashboard Report</h1>
        <div class="period">
            @if($period === 'custom')
                Period: {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
            @elseif($period === 'week')
                Period: This Week ({{ $startDate->format('M j') }} - {{ $endDate->format('M j, Y') }})
            @elseif($period === 'month')
                Period: This Month ({{ $startDate->format('F Y') }})
            @endif
        </div>
        <div class="generated">
            Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="label">Total Tickets</div>
                <div class="value">{{ number_format($dashboardData['total_tickets']) }}</div>
            </div>
            <div class="kpi-card warning">
                <div class="label">Open Tickets</div>
                <div class="value">{{ number_format($dashboardData['open_tickets']) }}</div>
            </div>
            <div class="kpi-card success">
                <div class="label">Resolved Tickets</div>
                <div class="value">{{ number_format($dashboardData['closed_tickets']) }}</div>
            </div>
            <div class="kpi-card danger">
                <div class="label">Overdue Tickets</div>
                <div class="value">{{ number_format($dashboardData['overdue_tickets']) }}</div>
            </div>
        </div>
    </div>

    <!-- Priority Distribution -->
    <div class="section">
        <h3 class="section-title">Priority Distribution</h3>
        <div class="chart-data">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($chartData['priority_distribution']['datasets'][0]['data']))
                        @foreach($chartData['priority_distribution']['labels'] as $index => $label)
                            @php
                                $count = $chartData['priority_distribution']['datasets'][0]['data'][$index] ?? 0;
                                $total = array_sum($chartData['priority_distribution']['datasets'][0]['data']);
                                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $count }}</td>
                                <td>{{ $percentage }}%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center">No data available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="section">
        <h3 class="section-title">Category Distribution</h3>
        <div class="chart-data">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($chartData['category_distribution']['datasets'][0]['data']))
                        @foreach($chartData['category_distribution']['labels'] as $index => $label)
                            @php
                                $count = $chartData['category_distribution']['datasets'][0]['data'][$index] ?? 0;
                                $total = array_sum($chartData['category_distribution']['datasets'][0]['data']);
                                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $count }}</td>
                                <td>{{ $percentage }}%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center">No data available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Technician Performance -->
    <div class="section">
        <h3 class="section-title">Technician Performance</h3>
        <div class="chart-data">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Technician</th>
                        <th>Total Assigned</th>
                        <th>Resolved</th>
                        <th>In Progress</th>
                        <th>Avg Resolution (Hours)</th>
                        <th>Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($chartData['technician_performance']['technician_metrics']))
                        @foreach($chartData['technician_performance']['technician_metrics'] as $tech)
                            <tr>
                                <td>{{ $tech['name'] }}</td>
                                <td>{{ $tech['total_assigned'] ?? 0 }}</td>
                                <td>{{ $tech['resolved'] ?? 0 }}</td>
                                <td>{{ $tech['in_progress'] ?? 0 }}</td>
                                <td>{{ $tech['avg_resolution'] ?? 0 }}</td>
                                <td>{{ $tech['efficiency'] ?? 0 }}%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">No technician data available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="section">
        <h3 class="section-title">Recent Ticket Activities</h3>
        <div class="chart-data">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dashboardData['recent_activities'] as $ticket)
                        <tr>
                            <td>{{ $ticket->ticket_number }}</td>
                            <td>{{ Str::limit($ticket->title, 30) }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'open' => 'badge-info',
                                        'in_progress' => 'badge-warning',
                                        'closed' => 'badge-success',
                                        'resolved' => 'badge-success',
                                        'pending' => 'badge-secondary'
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$ticket->status] ?? 'badge-secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $priorityClasses = [
                                        'low' => 'badge-success',
                                        'medium' => 'badge-info',
                                        'high' => 'badge-warning',
                                        'urgent' => 'badge-danger',
                                        'critical' => 'badge-danger'
                                    ];
                                @endphp
                                <span class="badge {{ $priorityClasses[$ticket->priority] ?? 'badge-secondary' }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>{{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned' }}</td>
                            <td>{{ $ticket->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary -->
    <div class="section">
        <h3 class="section-title">Summary</h3>
        <div class="two-column">
            <div class="column">
                <h4>Key Metrics</h4>
                <ul>
                    <li><strong>Total Tickets:</strong> {{ number_format($dashboardData['total_tickets']) }}</li>
                    <li><strong>Resolution Rate:</strong> 
                        {{ $dashboardData['total_tickets'] > 0 ? round(($dashboardData['closed_tickets'] / $dashboardData['total_tickets']) * 100, 1) : 0 }}%
                    </li>
                    <li><strong>Open Tickets:</strong> {{ number_format($dashboardData['open_tickets']) }}</li>
                    <li><strong>Overdue Tickets:</strong> {{ number_format($dashboardData['overdue_tickets']) }}</li>
                </ul>
            </div>
            <div class="column">
                <h4>Recommendations</h4>
                <ul>
                    @if($dashboardData['overdue_tickets'] > 0)
                        <li>Address {{ $dashboardData['overdue_tickets'] }} overdue tickets immediately</li>
                    @endif
                    @if($dashboardData['open_tickets'] > 10)
                        <li>Consider additional resources for {{ $dashboardData['open_tickets'] }} open tickets</li>
                    @endif
                    <li>Monitor technician performance regularly</li>
                    <li>Focus on high-priority tickets first</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function generatePDFReport() {
    console.log('Generating PDF report...');
    
    // Show loading state
    const exportBtn = document.getElementById('exportBtn');
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
    exportBtn.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('period', currentPeriod);
    
    if (currentStartDate && currentEndDate) {
        formData.append('start_date', currentStartDate);
        formData.append('end_date', currentEndDate);
    }
    
    // Create a form and submit it to trigger download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("manager.dashboard.export-pdf") }}';
    form.style.display = 'none';
    
    // Add form data as hidden inputs
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Reset button after a delay
    setTimeout(() => {
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
    }, 2000);
}
    </script>
</body>
</html>
