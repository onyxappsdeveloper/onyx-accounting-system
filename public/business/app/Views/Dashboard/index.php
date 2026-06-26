<?php
/**
 * Dashboard Module - Index View
 */
?>
<div class="dashboard-container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Dashboard</h2>
            <p class="text-muted">Welcome back, <?php echo e($user['name']); ?></p>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="metric-card" style="border-left-color: #27ae60;">
                <div class="label">Today's Sales</div>
                <div class="value">UGX <?php echo number_format($metrics['todaysSales'], 0); ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card" style="border-left-color: #3498db;">
                <div class="label">Monthly Sales</div>
                <div class="value">UGX <?php echo number_format($metrics['monthlySales'], 0); ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card" style="border-left-color: #e74c3c;">
                <div class="label">Today's Expenses</div>
                <div class="value">UGX <?php echo number_format($metrics['todaysExpenses'], 0); ?></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card" style="border-left-color: #f39c12;">
                <div class="label">Today's Profit</div>
                <div class="value">UGX <?php echo number_format($metrics['todaysProfit'], 0); ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Invoices</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInvoices as $invoice): ?>
                                <tr>
                                    <td><?php echo e($invoice['invoice_number']); ?></td>
                                    <td><?php echo e($invoice['customer_name']); ?></td>
                                    <td>UGX <?php echo number_format($invoice['total_amount'], 2); ?></td>
                                    <td><?php echo e($invoice['invoice_date']); ?></td>
                                    <td><span class="badge bg-<?php echo $invoice['status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo e($invoice['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
