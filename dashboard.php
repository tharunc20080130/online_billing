//dashboard.php
<?php
require 'config.php';
if (!isLoggedIn() || getUserRole($pdo) !== 'customer') {
    header('Location: customer_login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ AJAX HANDLER - Complete & Working
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        exit;
    }
    
    // Check if pending bill exists
    $stmt = $pdo->prepare("SELECT id FROM bills WHERE user_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $bill = $stmt->fetch();
    
    if (!$bill) {
        // Create new bill
        $stmt = $pdo->prepare("INSERT INTO bills (user_id, total) VALUES (?, 0.00)");
        $stmt->execute([$user_id]);
        $bill_id = $pdo->lastInsertId();
    } else {
        $bill_id = $bill['id'];
    }
    
    // Get product price
    $stmt = $pdo->prepare("SELECT price, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    // Insert into bill_items
    $stmt = $pdo->prepare("INSERT INTO bill_items (bill_id, product_id, quantity, price) VALUES (?, ?, 1, ?)");
    $stmt->execute([$bill_id, $product_id, $product['price']]);
    
    // Update bill total
    $stmt = $pdo->prepare("UPDATE bills SET total = (SELECT COALESCE(SUM(quantity * price), 0) FROM bill_items WHERE bill_id = ?) WHERE id = ?");
    $stmt->execute([$bill_id, $bill_id]);
    
    echo json_encode([
        'success' => true, 
        'bill_id' => $bill_id,
        'message' => 'Added ' . $product['name'] . ' to Bill #' . $bill_id
    ]);
    exit;
}

// Fetch data
$stmt = $pdo->prepare("SELECT b.*, COUNT(bi.id) as item_count FROM bills b LEFT JOIN bill_items bi ON b.id = bi.bill_id WHERE b.user_id = ? GROUP BY b.id ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$bills = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

// Bill details
$selected_bill_id = isset($_GET['bill']) ? (int)$_GET['bill'] : 0;
$selected_bill = null;
$bill_items = [];

if ($selected_bill_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM bills WHERE id = ? AND user_id = ?");
    $stmt->execute([$selected_bill_id, $user_id]);
    $selected_bill = $stmt->fetch();
    
    if ($selected_bill) {
        $stmt = $pdo->prepare("SELECT bi.*, p.name as product_name, p.image FROM bill_items bi JOIN products p ON bi.product_id = p.id WHERE bi.bill_id = ?");
        $stmt->execute([$selected_bill_id]);
        $bill_items = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #ffb300, #ff8a00) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .product-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            height: 100%;
            border-radius: 15px;
            overflow: hidden;
            background: white;
        }

        .product-card:hover {
            border-color: #ffb300;
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(255,179,0,0.3);
        }

        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .rupee {
            color: #ff8a00;
            font-weight: 700;
            font-size: 1.3em;
        }

        .btn-add-cart {
            background: linear-gradient(135deg, #ffb300, #ff8a00);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background: linear-gradient(135deg, #ff8a00, #ffb300);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,140,0,0.4);
            color: white;
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

        .bill-card {
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            background: white;
        }

        .bill-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #ffb300;
        }

        .bill-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            border-radius: 20px 20px 0 0;
            background: linear-gradient(135deg, #ffb300, #ff8a00);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand h5 mb-0">
                <i class="bi bi-cart4 me-2"></i>₹ Billing Dashboard
            </a>
            <a href="logout.php" class="btn btn-light btn-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- PRODUCTS -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #ffb300, #ff8a00);">
                        <h5 class="mb-0"><i class="bi bi-shop me-2"></i>Available Products - Click to Add</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-3 p-3">
                            <?php foreach ($products as $product): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card product-card h-100" onclick="addToBill(<?php echo $product['id']; ?>,'<?php echo addslashes($product['name']); ?>')">
                                    <img src="<?php echo $product['image']; ?>" class="product-img" alt="<?php echo $product['name']; ?>" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                    <div class="card-body">
                                        <h6 class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <div class="rupee mb-3">₹<?php echo number_format($product['price'], 0); ?></div>
                                        <button class="btn btn-add-cart w-100">
                                            <i class="bi bi-cart-plus me-2"></i>Add to Bill
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BILLS -->
            <div class="col-lg-4">
                <div class="card shadow sticky-top" style="top:20px;">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>My Bills (<?php echo count($bills); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bills)): ?>
                            <div class="text-center text-muted empty-state">
                                <i class="bi bi-receipt d-block mb-3"></i>
                                <h6>No bills yet</h6>
                                <p class="small">Click on products to create your first bill!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($bills as $bill): ?>
                            <a href="?bill=<?php echo $bill['id']; ?>" class="text-decoration-none">
                                <div class="bill-card mb-3 p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold mb-1">Bill #<?php echo $bill['id']; ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?php echo date('M j, Y', strtotime($bill['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo $bill['status']=='paid' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($bill['status']); ?>
                                            </span>
                                            <div class="rupee mt-2">₹<?php echo number_format($bill['total'], 2); ?></div>
                                            <small class="text-muted"><?php echo $bill['item_count']; ?> items</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- BILL DETAILS MODAL -->
        <?php if ($selected_bill): ?>
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt-cutoff me-2"></i>
                            Bill #<?php echo $selected_bill['id']; ?> - Details
                        </h5>
                        <a href="?" class="btn-close btn-close-white"></a>
                    </div>
                    <div class="modal-body">
                        <?php if (empty($bill_items)): ?>
                            <div class="text-center empty-state">
                                <i class="bi bi-inbox d-block mb-3 text-muted"></i>
                                <p class="text-muted">No items in this bill</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($bill_items as $item): ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-2">
                                    <img src="<?php echo $item['image']; ?>" class="bill-item-img" alt="<?php echo $item['product_name']; ?>">
                                </div>
                                <div class="col-6">
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                    <small class="text-muted">₹<?php echo number_format($item['price'], 0); ?> × <?php echo $item['quantity']; ?></small>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="rupee h5 mb-0">₹<?php echo number_format($item['quantity'] * $item['price'], 2); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div class="mt-4 p-4 rounded" style="background: linear-gradient(135deg, #fff5e6, #ffe4b3);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0 fw-bold">Grand Total:</span>
                                    <span class="rupee h3 mb-0">₹<?php echo number_format($selected_bill['total'], 2); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function addToBill(productId, productName) {
        console.log('Adding product:', productId, productName);
        
        const card = event.currentTarget;
        const originalHTML = card.innerHTML;
        
        // Show loading state with orange theme
        card.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border mb-3" style="width: 3rem; height: 3rem; color: #ffb300;"></div>
                <div class="h5 fw-bold" style="color: #ff8a00;">${productName}</div>
                <div class="text-muted">Adding to bill...</div>
            </div>
        `;
        
        // AJAX Request
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add_product&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data);
            
            if (data.success) {
                // Success animation with orange theme
                card.innerHTML = `
                    <div class="text-center p-4" style="color: #ff8a00;">
                        <i class="bi bi-check-circle-fill fs-1 mb-3"></i>
                        <div class="h5 fw-bold mb-2">${productName}</div>
                        <div class="alert alert-success m-2 border-0 shadow-sm">
                            ✅ ${data.message}
                        </div>
                        <small class="text-muted">Refreshing...</small>
                    </div>
                `;
                setTimeout(() => location.reload(), 1500);
            } else {
                card.innerHTML = originalHTML;
                alert('Error: ' + (data.error || 'Failed to add product'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            card.innerHTML = originalHTML;
            alert('Network error. Please try again.');
        });
    }
    </script>
</body>
</html>