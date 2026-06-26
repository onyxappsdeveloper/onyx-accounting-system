<?php
/**
 * Customers Module - Index View
 */
?>
<div class="customers-container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Customers</h2>
        </div>
        <div class="col-md-4 text-right">
            <a href="/business/customers/create" class="btn btn-primary">+ Add Customer</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo e($customer['name']); ?></td>
                            <td><?php echo e($customer['email']); ?></td>
                            <td><?php echo e($customer['phone']); ?></td>
                            <td><?php echo e($customer['city']); ?></td>
                            <td>
                                <a href="/business/customers/<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">View</a>
                                <a href="/business/customers/<?php echo $customer['id']; ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                                <button onclick="deleteItem(<?php echo $customer['id']; ?>, 'customers')" class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
