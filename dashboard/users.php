<?php
/**
 * User Management
 * 
 * Admin can manage users (view, create, edit, delete)
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in and has admin or manager role
requireLogin();
if (!hasRole('administrator') && !hasRole('manager')) {
    setAlert('danger', 'Access denied. You do not have permission to access this page.');
    header('Location: index.php');
    exit;
}

// Get database connection
$db = getDBConnection();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'create' || $action === 'edit') {
        // Get form data
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $role = trim($_POST['role']);
        $canGiveDiscount = isset($_POST['can_give_discount']) ? 1 : 0;
        
        // Validate form data
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is invalid';
        }
        
        if (empty($role)) {
            $errors[] = 'Role is required';
        }
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM members WHERE email = :email AND id != :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email already exists';
        }
        
        // If there are no errors, create or update user
        if (empty($errors)) {
            if ($action === 'create') {
                // Generate a random password
                $password = generateRandomString(10);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Create user
                $stmt = $db->prepare("INSERT INTO members (name, email, phone, password, address, role, can_give_discount) 
                                    VALUES (:name, :email, :phone, :password, :address, :role, :canGiveDiscount)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':canGiveDiscount', $canGiveDiscount);
                
                if ($stmt->execute()) {
                    $userId = $db->lastInsertId();
                    setAlert('success', 'User created successfully. Temporary password: ' . $password);
                    
                    // Add notification for new user registration
                    $currentUser = getCurrentUser();
                    $message = $name . ' has been registered by ' . $currentUser['name'];
                    addNotification('register', $message, $userId);
                    
                    header('Location: users.php');
                    exit;
                } else {
                    setAlert('danger', 'Failed to create user');
                }
            } else {
                // Update user
                $stmt = $db->prepare("UPDATE members SET name = :name, email = :email, phone = :phone, 
                                    address = :address, role = :role, can_give_discount = :canGiveDiscount 
                                    WHERE id = :id");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':canGiveDiscount', $canGiveDiscount);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    setAlert('success', 'User updated successfully');
                    header('Location: users.php');
                    exit;
                } else {
                    setAlert('danger', 'Failed to update user');
                }
            }
        } else {
            setAlert('danger', implode('<br>', $errors));
        }
    } elseif ($action === 'delete') {
        // Delete user
        $id = (int)$_POST['id'];
        
        // Make sure user is not deleting themselves
        if ($id === $_SESSION['user_id']) {
            setAlert('danger', 'You cannot delete your own account');
            header('Location: users.php');
            exit;
        }
        
        // Make sure user is not deleting the last administrator
        $stmt = $db->prepare("SELECT role FROM members WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $role = $stmt->fetchColumn();
        
        if ($role === 'administrator') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM members WHERE role = 'administrator'");
            $stmt->execute();
            $adminCount = $stmt->fetchColumn();
            
            if ($adminCount <= 1) {
                setAlert('danger', 'Cannot delete the last administrator');
                header('Location: users.php');
                exit;
            }
        }
        
        // Delete user
        $stmt = $db->prepare("DELETE FROM members WHERE id = :id");
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            setAlert('success', 'User deleted successfully');
        } else {
            setAlert('danger', 'Failed to delete user');
        }
        
        header('Location: users.php');
        exit;
    } elseif ($action === 'reset_password') {
        // Reset user password
        $id = (int)$_POST['id'];
        
        // Generate a new random password
        $password = generateRandomString(10);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $stmt = $db->prepare("UPDATE members SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Password reset successfully. New password: ' . $password);
        } else {
            setAlert('danger', 'Failed to reset password');
        }
        
        header('Location: users.php');
        exit;
    }
}

// Get action and ID from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables for form
$user = [
    'id' => 0,
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'role' => 'client',
    'can_give_discount' => 0
];

// If editing, get user data
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM members WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $fetchedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($fetchedUser) {
        $user = $fetchedUser;
    } else {
        setAlert('danger', 'User not found');
        header('Location: users.php');
        exit;
    }
}

// Get all users for list view
$users = [];
if ($action === '' || $action === 'list') {
    $stmt = $db->query("SELECT * FROM members ORDER BY name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Set page title based on action
$pageTitle = 'User Management';
if ($action === 'create') {
    $pageTitle = 'Create User';
} elseif ($action === 'edit') {
    $pageTitle = 'Edit User';
}

// Include header
require_once '../templates/header.php';
?>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?php echo $pageTitle; ?></h4>
            <?php if ($action === '' || $action === 'list'): ?>
                <a href="users.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create User
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row bg-light rounded align-items-center justify-content-center p-3 mx-1">
        <?php if ($action === 'create' || $action === 'edit'): ?>
            <!-- Create/Edit Form -->
            <div class="col-12">
                <form method="post" action="users.php?action=<?php echo $action; ?>" class="row g-3">
                    <?php if ($id > 0): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                            <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                            <option value="administrator" <?php echo $user['role'] === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_give_discount" name="can_give_discount" <?php echo $user['can_give_discount'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="can_give_discount">
                                Can give discount to bookings
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="users.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- User List -->
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Discount</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['role'] === 'administrator' ? 'danger' : 
                                                    ($user['role'] === 'manager' ? 'warning' : 'info'); 
                                            ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['can_give_discount']): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <!-- Reset Password Button -->
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal<?php echo $user['id']; ?>">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                
                                                <!-- Delete Button -->
                                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Reset Password Modal -->
                                            <div class="modal fade" id="resetPasswordModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="resetPasswordModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="resetPasswordModalLabel<?php echo $user['id']; ?>">Reset Password</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to reset the password for <strong><?php echo htmlspecialchars($user['name']); ?></strong>?</p>
                                                            <p>A new random password will be generated.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="post" action="users.php?action=reset_password">
                                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                                <button type="submit" class="btn btn-warning">Reset Password</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Delete Modal -->
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $user['id']; ?>">Delete User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete <strong><?php echo htmlspecialchars($user['name']); ?></strong>?</p>
                                                                <p>This action cannot be undone.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post" action="users.php?action=delete">
                                                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>