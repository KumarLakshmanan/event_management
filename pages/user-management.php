<?php
require_once '../includes/header.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Get mock data
$users = getMockData('users.json');

// Get user for edit if specified
$editUser = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    foreach ($users as $user) {
        if ($user['id'] == $_GET['edit']) {
            $editUser = $user;
            break;
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Management</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#userModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New User
    </a>
</div>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Users List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Can Give Discount</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['phone'] ?? 'N/A'; ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge badge-primary">Admin</span>
                            <?php elseif ($user['role'] === 'manager'): ?>
                                <span class="badge badge-info">Manager</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Client</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['can_give_discount']): ?>
                                <span class="badge badge-success">Yes</span>
                            <?php else: ?>
                                <span class="badge badge-danger">No</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="../handlers/users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No users found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel"><?php echo $editUser ? 'Edit User' : 'Add New User'; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="userForm" class="api-form" action="../handlers/users.php" method="POST" data-redirect="user-management.php">
                    <input type="hidden" name="action" value="<?php echo $editUser ? 'update' : 'create'; ?>">
                    <?php if ($editUser): ?>
                    <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $editUser ? $editUser['name'] : ''; ?>" required>
                        <div id="nameFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $editUser ? $editUser['email'] : ''; ?>" required>
                        <div id="emailFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $editUser ? $editUser['phone'] : ''; ?>">
                        <div id="phoneFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <?php if (!$editUser): ?>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div id="passwordFeedback" class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                            <div id="confirmPasswordFeedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $editUser ? $editUser['address'] : ''; ?></textarea>
                        <div id="addressFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="client" <?php echo ($editUser && $editUser['role'] === 'client') ? 'selected' : ''; ?>>Client</option>
                            <option value="manager" <?php echo ($editUser && $editUser['role'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="admin" <?php echo ($editUser && $editUser['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                        <div id="roleFeedback" class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="can_give_discount" name="can_give_discount" value="1" <?php echo ($editUser && $editUser['can_give_discount']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="can_give_discount">Can Give Discount</label>
                        </div>
                        <small class="form-text text-muted">Only applicable for Managers and Admins. Gives permission to apply discounts when confirming bookings.</small>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><?php echo $editUser ? 'Update User' : 'Add User'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Auto open modal if edit parameter is present -->
<?php if ($editUser): ?>
<script>
$(document).ready(function() {
    $('#userModal').modal('show');
    
    // Hide role field if editing your own account
    if (<?php echo $editUser['id']; ?> == <?php echo $_SESSION['user_id']; ?>) {
        $('#role').prop('disabled', true);
        $('#role').parent().append('<div class="text-info">You cannot change your own role.</div>');
    }
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
