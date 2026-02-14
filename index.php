//index.php
<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        .hero {
            background: linear-gradient(135deg, #ffb300 0%, #ff8a00 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background particles */
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.15), transparent 60%),
                        radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1), transparent 60%);
            animation: float 20s infinite alternate ease-in-out;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-30px) rotate(5deg); }
        }

        .main-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-title {
            animation: fadeInDown 1s ease-out 0.2s both;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-subtitle {
            animation: fadeIn 1s ease-out 0.4s both;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 300;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .btn-hero {
            padding: 20px 40px;
            font-size: 1.3rem;
            font-weight: 600;

            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .btn-hero:active {
            transform: translateY(-2px) scale(1.02);
        }

        .btn-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-admin:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }

        .btn-customer {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-customer:hover {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            color: white;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease-out;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .login-section {
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .btn-outline-custom {
            border: 2px solid rgba(255, 255, 255, 0.8);
            color: white;
            transition: all 0.3s;
        }

        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: white;
            color: white;
            transform: translateY(-2px);
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .btn-hero {
                padding: 15px 30px;
                font-size: 1.1rem;
                margin: 10px 0;
            }
            
            .feature-card {
                margin: 10px 0;
                padding: 20px;
            }
        }

        /* Loading animation */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #ffb300 0%, #ff8a00 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .loading.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top: 5px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #ffb300, #ff8a00);
            color: white;
            border-radius: 20px 20px 0 0;
        }
    </style>
</head>
<body>
    <div class="hero text-white">
        <div class="main-content text-center">
            <h1 class="display-2 mb-4 fw-bold hero-title">
                <i class="bi bi-receipt me-3"></i>
                Online Billing System
            </h1>
            <p class="lead hero-subtitle mb-5">
                Streamlined billing solutions for modern businesses
            </p>
                        <!-- Login Buttons -->
            <div class="login-section">
                <h3 class="mb-4 fw-bold">Choose Your Access Level</h3>
                <div class="row justify-content-center g-4">
                    <div class="col-md-5">
                        <a href="admin_login.php" class="btn btn-admin btn-hero w-100">
                            <i class="bi bi-shield-lock me-3"></i>
                            <div>
                                <div class="fw-bold">Admin Portal</div>
                                <small class="d-block">System Administration</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-5">
                        <a href="customer_login.php" class="btn btn-customer btn-hero w-100">
                            <i class="bi bi-person-circle me-3"></i>
                            <div>
                                <div class="fw-bold">Customer Portal</div>
                                <small class="d-block">View & Pay Bills</small>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Additional Options -->
                <div class="row justify-content-center mt-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#aboutModal">
                                <i class="bi bi-info-circle me-1"></i>About
                            </button>
                            <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#helpModal">
                                <i class="bi bi-question-circle me-1"></i>Help
                            </button>
                            <button class="btn btn-outline-custom btn-sm" data-bs-toggle="modal" data-bs-target="#supportModal">
                                <i class="bi bi-telephone me-1"></i>Support
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Loading Screen -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>


    <!-- About Modal -->
    <div class="modal fade" id="aboutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>About Our System</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Our Online Billing System provides a comprehensive solution for managing customer bills, payments, and administrative tasks with ease and security.</p>
                    <ul>
                        <li>Real-time billing management</li>
                        <li>Secure payment processing</li>
                        <li>Detailed analytics and reporting</li>
                        <li>Mobile-friendly interface</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

  
    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-question-circle me-2"></i>Need Help?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold"><i class="bi bi-person-circle me-2"></i>For Customers:</h6>
                    <p>Use the Customer Portal to view your bills, make payments, and track your account history.</p>
                    
                    <h6 class="fw-bold"><i class="bi bi-shield-lock me-2"></i>For Administrators:</h6>
                    <p>Access the Admin Portal to manage customer accounts, generate bills, and view system analytics.</p>
                    
                    <div class="alert alert-info mt-3">
                        <strong><i class="bi bi-envelope me-2"></i>Need more help?</strong>
                        <p class="mb-0">Contact our support team using the Support button below.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Modal -->
    <div class="modal fade" id="supportModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-headset me-2"></i>Contact Support</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-envelope-fill" style="font-size: 3rem; color: #ffb300;"></i>
                    </div>
                    
                    <h6 class="fw-bold mb-3">Get in Touch</h6>
                    <p>Our support team is here to help you with any questions or issues.</p>
                    
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="bi bi-person-badge me-2"></i>General Support
                            </h6>
                            <a href="mailto:support@billing.com" class="text-decoration-none">
                                <i class="bi bi-envelope me-2"></i>support@billing.com
                            </a>
                        </div>
                    </div>

                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="fw-bold text-danger mb-2">
                                <i class="bi bi-shield-lock me-2"></i>Admin Support
                            </h6>
                            <a href="mailto:admin@billing.com" class="text-decoration-none">
                                <i class="bi bi-envelope me-2"></i>admin@billing.com
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4 mb-0">
                        <small><i class="bi bi-clock me-2"></i><strong>Support Hours:</strong> Mon-Fri, 9 AM - 6 PM</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loading screen
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loading').classList.add('fade-out');
            }, 1000);
        });

        // Add hover effects
        document.querySelectorAll('.btn-hero').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.05)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
            
            // Click ripple effect
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255,255,255,0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>