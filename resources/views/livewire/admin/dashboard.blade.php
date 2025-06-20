<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Admin Dashboard</h1>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetStatsOrder()">
            <i class="bi bi-arrow-clockwise"></i> Reset Layout
        </button>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_users']) }}</h4>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['active_users']) }}</h4>
                            <p class="mb-0">Active Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-person-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_tickets']) }}</h4>
                            <p class="mb-0">Total Tickets</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-ticket-perforated fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['open_tickets']) }}</h4>
                            <p class="mb-0">Open Tickets</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-exclamation-triangle fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <!-- Ticket Status Distribution -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ticket Status Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="mb-2">
                                <span class="badge bg-warning fs-6">{{ $stats['open_tickets'] }}</span>
                            </div>
                            <small class="text-muted">Open</small>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <span class="badge bg-info fs-6">{{ $stats['in_progress_tickets'] }}</span>
                            </div>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <span class="badge bg-success fs-6">{{ $stats['closed_tickets'] }}</span>
                            </div>
                            <small class="text-muted">Closed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- Priority Tickets -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Priority Tickets</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="mb-2">
                                <span class="badge bg-danger fs-6">{{ $stats['high_priority_tickets'] }}</span>
                            </div>
                            <small class="text-muted">Urgent</small>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <span class="badge bg-warning fs-6">{{ $stats['overdue_tickets'] }}</span>
                            </div>
                            <small class="text-muted">Overdue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Timeline -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ticket Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <h4 class="mb-0 text-primary">{{ $stats['tickets_today'] }}</h4>
                            </div>
                            <small class="text-muted">Today</small>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <h4 class="mb-0 text-info">{{ $stats['tickets_this_week'] }}</h4>
                            </div>
                            <small class="text-muted">This Week</small>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <h4 class="mb-0 text-success">{{ $stats['tickets_this_month'] }}</h4>
                            </div>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview -->
    <div class="row">
        <div class="col-md-8">
            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    @if($stats['recent_activities']->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($stats['recent_activities'] as $activity)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $activity->description }}</h6>
                                            <p class="mb-1 text-muted small">
                                                @if($activity->causer)
                                                    by {{ $activity->causer->name }}
                                                @else
                                                    System
                                                @endif
                                            </p>
                                        </div>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No recent activities</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- System Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Overview</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Categories</span>
                            <span class="badge bg-secondary">{{ $stats['total_categories'] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Roles</span>
                            <span class="badge bg-secondary">{{ $stats['total_roles'] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>System Status</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function resetStatsOrder() {
        localStorage.removeItem('stats-card-order');
        location.reload();
    }
</script>