// admin.php
<?php
require 'config.php';
if (!isLoggedIn() || getUserRole($pdo) !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $details = trim($_POST['details'] ?? '');
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '.' . strtolower($ext);
        $target = 'images/' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image = $target;
    } else {
        $image = 'images/default.jpg';
    }
    
    $stmt = $pdo->prepare("INSERT INTO products (name, price, image, details) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $image, $details]);
    header('Location: admin.php?tab=products&added=1');
    exit;
}

if (isset($_GET['delete_product'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete_product']]);
    header('Location: admin.php?tab=products&deleted=1');
    exit;
}

// Handle customer delete
if (isset($_GET['delete_customer'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$_GET['delete_customer']]);
    header('Location: admin.php?tab=customers&deleted=1');
    exit;
}

// Handle bill status update
if (isset($_GET['update_bill_status'])) {
    $bill_id = (int)$_GET['update_bill_status'];
    $status = $_GET['status'] === 'paid' ? 'paid' : 'pending';
    $stmt = $pdo->prepare("UPDATE bills SET status = ? WHERE id = ?");
    $stmt->execute([$status, $bill_id]);
    header('Location: admin.php?tab=bills&updated=1');
    exit;
}

// Fetch dashboard stats
$total_bills = $pdo->query("SELECT COUNT(*) FROM bills")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total) FROM bills WHERE status = 'paid'")->fetchColumn() ?: 0;

// Fetch data by tabs
$tab = $_GET['tab'] ?? 'dashboard';

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$customers = $pdo->query("SELECT id, username, email, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();

// Bills with customer info
$bills = $pdo->query("
    SELECT b.*, u.username 
    FROM bills b 
    LEFT JOIN users u ON b.user_id = u.id 
    ORDER BY b.created_at DESC 
    LIMIT 50
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #ffb300, #ff8a00) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .stats-card {
            border-radius: 15px;
            transition: all 0.3s;
            border: none;
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.3;
        }

        .nav-tabs {
            border-bottom: 3px solid #ffb300;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
            color: #555;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link:hover {
            background: rgba(255,179,0,0.1);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #ffb300, #ff8a00);
            color: white;
            transform: translateY(-3px);
        }

        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
            border: none;
            font-weight: 600;
        }

        .product-card {
            transition: all 0.3s;
            border-radius: 12px;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #ffb300, #ff8a00);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #ff8a00, #ffb300);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,140,0,0.4);
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark shadow-lg">
        <div class="container-fluid">
            <a class="navbar-brand h4 mb-0">
                <i class="bi bi-house-gear me-2"></i>Admin Dashboard
            </a>
            <div class="d-flex gap-3 align-items-center">
                <span class="navbar-text text-white fw-bold">
                    🧾 <?php echo $total_bills; ?> | 👥 <?php echo $total_customers; ?> | 📦 <?php echo $total_products; ?>
                </span>
                <a href="logout.php" class="btn btn-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4 px-4">
        <!-- Stats Dashboard -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card bg-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-primary fw-bold small mb-1">Total Bills</div>
                                <div class="h3 mb-0 fw-bold text-dark"><?php echo $total_bills; ?></div>
                            </div>
                            <i class="bi bi-receipt stats-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card bg-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-success fw-bold small mb-1">Customers</div>
                                <div class="h3 mb-0 fw-bold text-dark"><?php echo $total_customers; ?></div>
                            </div>
                            <i class="bi bi-people stats-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card bg-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-info fw-bold small mb-1">Products</div>
                                <div class="h3 mb-0 fw-bold text-dark"><?php echo $total_products; ?></div>
                            </div>
                            <i class="bi bi-boxes stats-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card bg-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-warning fw-bold small mb-1">Revenue</div>
                                <div class="h3 mb-0 fw-bold text-dark">₹<?php echo number_format($total_revenue, 0); ?></div>
                            </div>
                            <i class="bi bi-currency-rupee stats-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo $tab=='dashboard' ? 'active' : ''; ?>" href="?tab=dashboard">
                    📊 Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $tab=='products' ? 'active' : ''; ?>" href="?tab=products">
                    📦 Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $tab=='customers' ? 'active' : ''; ?>" href="?tab=customers">
                    👥 Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $tab=='bills' ? 'active' : ''; ?>" href="?tab=bills">
                    🧾 Bills (<?php echo $total_bills; ?>)
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <?php if ($tab === 'dashboard'): ?>
        <div class="alert alert-light border-0 shadow-sm">
            <h5 class="mb-2"><i class="bi bi-info-circle text-primary"></i> Welcome to Admin Dashboard!</h5>
            <p class="mb-0">Use the tabs above to manage Products, Customers, and Bills. Track revenue and business performance from here.</p>
        </div>

        <?php elseif ($tab === 'products'): ?>
        <!-- PRODUCTS SECTION -->
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success shadow-sm">✅ Product added successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-danger shadow-sm">✅ Product deleted!</div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Product</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Product Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Laptop" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Price (₹) *</label>
                            <input type="number" name="price" step="0.01" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Product Image</label>
                            <input type="file" name="image" accept="image/*" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Details</label>
                            <textarea name="details" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #ffb300, #ff8a00);">
                <h5 class="mb-0">📦 Products List (<?php echo count($products); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card product-card h-100">
                            <img src="<?php echo $product['image']; ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></h6>
                                <div class="fw-bold text-success fs-5">₹<?php echo number_format($product['price'], 0); ?></div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="?tab=products&delete_product=<?php echo $product['id']; ?>" 
                                   class="btn btn-danger w-100" 
                                   onclick="return confirm('Delete this product?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php elseif ($tab === 'customers'): ?>
        <!-- CUSTOMERS SECTION -->
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success shadow-sm">✅ Customer deleted!</div>
        <?php endif; ?>
        <div class="card shadow">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                <h5 class="mb-0">👥 Customer List (<?php echo count($customers); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><strong>#<?php echo $customer['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                                <td>
                                    <a href="?tab=customers&delete_customer=<?php echo $customer['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Delete <?php echo htmlspecialchars($customer['username']); ?>?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($tab === 'bills'): ?>
        <!-- BILLS SECTION -->
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success shadow-sm">✅ Bill status updated!</div>
        <?php endif; ?>
        <div class="card shadow">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Bill Management (<?php echo count($bills); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Bill ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><strong>#<?php echo $bill['id']; ?></strong></td>
                                <td><?php echo $bill['username'] ?: 'Guest'; ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($bill['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bill_items WHERE bill_id = ?");
                                    $stmt->execute([$bill['id']]);
                                    echo $stmt->fetchColumn();
                                    ?> items
                                </td>
                                <td><strong class="text-success">₹<?php echo number_format($bill['total'], 2); ?></strong></td>
                                <td>
                                    <span class="badge bg-<?php echo $bill['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($bill['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($bill['status'] === 'pending'): ?>
                                        <a href="?tab=bills&update_bill_status=<?php echo $bill['id']; ?>&status=paid" 
                                           class="btn btn-success btn-sm me-1">
                                            <i class="bi bi-check-lg"></i> Mark Paid
                                        </a>
                                    <?php endif; ?>
                                    <a href="?tab=bills&delete_bill=<?php echo $bill['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Delete Bill #<?php echo $bill['id']; ?>?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>