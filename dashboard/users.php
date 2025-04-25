<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../models/User.php';

// Require login for all actions
requireLogin();

// Check if user has admin permission
if (!hasRole(ROLE_ADMIN)) {
    setFlashMessage('You do not have permission to access user management.', 'danger');
    header('Location: ' . APP_URL . 'dashboard/index.php');
    exit;
}

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$userController = new UserController();

// Get the current user
$user = getCurrentUser();

// Check for user ID in URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get role filter from URL
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// Initialize variables
$errors = [];
$success = false;
$userData = [];

// Process user actions (create/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle create user form submission
    if (isset($_POST['create_user'])) {
        $userData = [
            'name' => sanitizeInput($_POST['name']),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'role' => sanitizeInput($_POST['role']),
            'can_apply_discount' => isset($_POST['can_apply_discount']) ? 1 : 0
        ];
        
        // Validate email
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Validate password
        if (empty($userData['password'])) {
            $errors[] = 'Password is required.';
        } elseif (strlen($userData['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        } elseif ($userData['password'] !== $userData['confirm_password']) {
            $errors[] = 'Passwords do not match.';
        }
        
        // Validate role
        $validRoles = [ROLE_ADMIN, ROLE_MANAGER, ROLE_CLIENT];
        if (!in_array($userData['role'], $validRoles)) {
            $errors[] = 'Please select a valid role.';
        }
        
        if (empty($errors)) {
            $newUserId = $userController->createUser($userData);
            
            if ($newUserId) {
                setFlashMessage('User created successfully!', 'success');
                header('Location: ' . APP_URL . 'dashboard/users.php');
                exit;
            } else {
                $errors[] = 'Failed to create user. The email may already be in use.';
            }
        }
    }
    
    // Handle edit user form submission
    if (isset($_POST['edit_user'])) {
        $userData = [
            'id' => (int)$_POST['user_id'],
            'name' => sanitizeInput($_POST['name']),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'role' => sanitizeInput($_POST['role']),
            'can_apply_discount' => isset($_POST['can_apply_discount']) ? 1 : 0
        ];
        
        // Check if password is being updated
        if (!empty($_POST['password'])) {
            $userData['password'] = $_POST['password'];
            $userData['confirm_password'] = $_POST['confirm_password'];
            
            // Validate password
            if (strlen($userData['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            } elseif ($userData['password'] !== $userData['confirm_password']) {
                $errors[] = 'Passwords do not match.';
            }
        }
        
        // Validate email
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Validate role
        $validRoles = [ROLE_ADMIN, ROLE_MANAGER, ROLE_CLIENT];
        if (!in_array($userData['role'], $validRoles)) {
            $errors[] = 'Please select a valid role.';
        }
        
        if (empty($errors)) {
            $result = $userController->updateUser($userData);
            
            if ($result) {
                setFlashMessage('User updated successfully!', 'success');
                header('Location: ' . APP_URL . 'dashboard/users.php');
                exit;
            } else {
                $errors[] = 'Failed to update user. The email may already be in use.';
            }
        }
    }
    
    // Handle delete user form submission
    if (isset($_POST['delete_user'])) {
        $userId = (int)$_POST['user_id'];
        
        // Cannot delete yourself
        if ($userId === (int)$user['id']) {
            setFlashMessage('You cannot delete your own account.', 'danger');
            header('Location: ' . APP_URL . 'dashboard/users.php');
            exit;
        }
        
        $result = $userController->deleteUser($userId);
        
        if ($result) {
            setFlashMessage('User deleted successfully!', 'success');
            header('Location: ' . APP_URL . 'dashboard/users.php');
            exit;
        } else {
            $errors[] = 'Failed to delete user.';
        }
    }
}

// Handle different page actions
if ($action === 'create') {
    // Create new user
    $pageTitle = 'Create User';
    $template = 'create';
} elseif ($action === 'edit' && $userId) {
    // Edit existing user
    $userData = $userController->getUserById($userId);
    if (!$userData) {
        setFlashMessage('User not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/users.php');
        exit;
    }
    $pageTitle = 'Edit User';
    $template = 'edit';
} elseif ($action === 'delete' && $userId) {
    // Confirm delete user
    $userData = $userController->getUserById($userId);
    if (!$userData) {
        setFlashMessage('User not found.', 'danger');
        header('Location: ' . APP_URL . 'dashboard/users.php');
        exit;
    }
    $pageTitle = 'Delete User';
    $template = 'delete';
} else {
    // List all users
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Get users with optional role filter
    if (!empty($roleFilter)) {
        $result = $userController->getUsersByRole($roleFilter, $page);
    } else {
        $result = $userController->getAllUsers($page);
    }
    
    $users = $result['users'];
    $pagination = $result['pagination'];
    $pageTitle = 'User Management';
    $template = 'list';
}

// Set up page title and sidebar flag for template
$title = $pageTitle;
$showSidebar = true;

// Include header
include_once __DIR__ . '/../templates/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?php echo $pageTitle; ?></h1>
        
        <?php if ($template === 'list'): ?>
            <a href="<?php echo APP_URL; ?>dashboard/users.php?action=create" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Create New User
            </a>
        <?php elseif ($template !== 'list'): ?>
            <a href="<?php echo APP_URL; ?>dashboard/users.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($template === 'list'): ?>
        <!-- Role filter for user list -->
        <div class="mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <h5 class="card-title mb-md-0">Filter by Role</h5>
                        </div>
                        <div class="col-md-10">
                            <div class="btn-group">
                                <a href="<?php echo APP_URL; ?>dashboard/users.php" class="btn <?php echo $roleFilter === '' ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                                <a href="<?php echo APP_URL; ?>dashboard/users.php?role=<?php echo ROLE_ADMIN; ?>" class="btn <?php echo $roleFilter === ROLE_ADMIN ? 'btn-primary' : 'btn-outline-primary'; ?>">Administrators</a>
                                <a href="<?php echo APP_URL; ?>dashboard/users.php?role=<?php echo ROLE_MANAGER; ?>" class="btn <?php echo $roleFilter === ROLE_MANAGER ? 'btn-primary' : 'btn-outline-primary'; ?>">Managers</a>
                                <a href="<?php echo APP_URL; ?>dashboard/users.php?role=<?php echo ROLE_CLIENT; ?>" class="btn <?php echo $roleFilter === ROLE_CLIENT ? 'btn-primary' : 'btn-outline-primary'; ?>">Clients</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User List View -->
        <div class="card">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">
                            <?php if (!empty($roleFilter)): ?>
                                <?php echo getRoleName($roleFilter); ?> Users
                            <?php else: ?>
                                All Users
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="col-auto">
                        <form class="d-flex" action="" method="GET">
                            <input type="text" class="form-control me-2" name="search" placeholder="Search users..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="p-4 text-center">
                        <p>No users found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Permissions</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span class="badge role-badge 
                                                  <?php
                                                    echo $u['role'] === ROLE_ADMIN ? 'bg-danger' :
                                                        ($u['role'] === ROLE_MANAGER ? 'bg-warning' : 'bg-info');
                                                  ?>">
                                                <?php echo getRoleName($u['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($u['role'] !== ROLE_CLIENT): ?>
                                                <?php if ((int)$u['can_apply_discount'] === 1): ?>
                                                    <span class="badge bg-success">Can Apply Discounts</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No Discount Permission</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($u['created_at'], 'M j, Y'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>dashboard/users.php?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                                                    <a href="<?php echo APP_URL; ?>dashboard/users.php?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-outline-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-secondary" disabled title="You cannot delete your own account">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($pagination['total'] > 1): ?>
                <div class="card-footer">
                    <?php 
                    $pageUrl = '/dashboard/users.php';
                    if (!empty($roleFilter)) {
                        $pageUrl .= '?role=' . $roleFilter . '&page=';
                    } else {
                        $pageUrl .= '?page=';
                    }
                    echo getPagination($pagination['current'], $pagination['total'], $pageUrl); 
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
    <?php elseif ($template === 'create' || $template === 'edit'): ?>
        <!-- Create/Edit User Form -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><?php echo $template === 'create' ? 'Create New User' : 'Edit User'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php if ($template === 'edit'): ?>
                        <input type="hidden" name="user_id" value="<?php echo $userData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo isset($userData['name']) ? htmlspecialchars($userData['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="<?php echo ROLE_CLIENT; ?>" <?php echo isset($userData['role']) && $userData['role'] === ROLE_CLIENT ? 'selected' : ''; ?>>
                                    Client
                                </option>
                                <option value="<?php echo ROLE_MANAGER; ?>" <?php echo isset($userData['role']) && $userData['role'] === ROLE_MANAGER ? 'selected' : ''; ?>>
                                    Manager
                                </option>
                                <option value="<?php echo ROLE_ADMIN; ?>" <?php echo isset($userData['role']) && $userData['role'] === ROLE_ADMIN ? 'selected' : ''; ?>>
                                    Administrator
                                </option>
                            </select>
                            <div id="discount-permission-container" class="form-check mt-2" style="<?php echo (!isset($userData['role']) || $userData['role'] === ROLE_CLIENT) ? 'display:none;' : ''; ?>">
                                <input class="form-check-input" type="checkbox" id="can_apply_discount" name="can_apply_discount" value="1" 
                                       <?php echo isset($userData['can_apply_discount']) && (int)$userData['can_apply_discount'] === 1 ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="can_apply_discount">
                                    Allow user to apply discounts
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                <?php echo $template === 'create' ? 'Password' : 'New Password (leave blank to keep current)'; ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   <?php echo $template === 'create' ? 'required' : ''; ?>>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   <?php echo $template === 'create' ? 'required' : ''; ?>>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>dashboard/users.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="<?php echo $template === 'create' ? 'create_user' : 'edit_user'; ?>" class="btn btn-primary">
                            <?php echo $template === 'create' ? 'Create User' : 'Update User'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    <?php elseif ($template === 'delete'): ?>
        <!-- Delete User Confirmation -->
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">Confirm Deletion</h5>
            </div>
            <div class="card-body">
                <p>Are you sure you want to delete the user <strong><?php echo htmlspecialchars($userData['name']); ?></strong>?</p>
                <p>This action cannot be undone. All data associated with this user will be permanently removed.</p>
                
                <div class="alert alert-warning">
                    <h6>User Details:</h6>
                    <ul class="mb-0">
                        <li><strong>Name:</strong> <?php echo htmlspecialchars($userData['name']); ?></li>
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></li>
                        <li><strong>Role:</strong> <?php echo getRoleName($userData['role']); ?></li>
                    </ul>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?php echo $userData['id']; ?>">
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>dashboard/users.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="delete_user" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i>Confirm Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../templates/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dynamic show/hide of discount permission checkbox based on role selection
        const roleSelect = document.getElementById('role');
        const discountPermissionContainer = document.getElementById('discount-permission-container');
        
        if (roleSelect && discountPermissionContainer) {
            // Create container for discount checkbox if it doesn't exist
            if (!document.getElementById('discount-permission-container')) {
                const container = document.createElement('div');
                container.id = 'discount-permission-container';
                container.className = 'form-check mt-2';
                container.innerHTML = `
                    <input class="form-check-input" type="checkbox" id="can_apply_discount" name="can_apply_discount" value="1">
                    <label class="form-check-label" for="can_apply_discount">
                        Allow user to apply discounts
                    </label>
                `;
                roleSelect.parentNode.appendChild(container);
            }
            
            // Function to toggle discount permission visibility
            const toggleDiscountPermission = function() {
                const role = roleSelect.value;
                const container = document.getElementById('discount-permission-container');
                
                if (role === '<?php echo ROLE_MANAGER; ?>' || role === '<?php echo ROLE_ADMIN; ?>') {
                    container.style.display = 'block';
                    // Auto-check for administrators
                    if (role === '<?php echo ROLE_ADMIN; ?>') {
                        document.getElementById('can_apply_discount').checked = true;
                    }
                } else {
                    container.style.display = 'none';
                    document.getElementById('can_apply_discount').checked = false;
                }
            };
            
            // Initial call
            toggleDiscountPermission();
            
            // Add event listener for changes
            roleSelect.addEventListener('change', toggleDiscountPermission);
        }
    });
</script>
