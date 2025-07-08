<?php
$plans = [
    'GOVERNMENT' => [
        'title' => 'Government Plan',
        'price' => 4999,
        'icon' => 'fa-landmark',
        'features' => [
            'Complete documentation assistance',
            'Personalised admission roadmap',
            'Counselling & choice filling guidance',
            'Smart preference list',
            'WhatsApp support'
        ]
    ],
    'SEMI-GOVERNMENT' => [
        'title' => 'Semi-Government Plan',
        'price' => 9999,
        'icon' => 'fa-hospital',
        'features' => [
            'Everything in Government Plan',
            'College & branch predictor tool',
            'Mock choice filling sessions',
            'Priority support during key dates',
            'Cut-off trend analysis',
            'Updates on Mop-Up & Stray rounds'
        ]
    ],
    'PRIVATE' => [
        'title' => 'Private Plan',
        'price' => 24999,
        'icon' => 'fa-building-columns',
        'features' => [
            'Everything in Semi-Govt Plan',
            'Full support for all counselling bodies',
            '3 expert counselling sessions',
            'Custom preference list',
            '7-day-a-week dedicated counsellor',
            'Post-admission support'
        ]
    ],
    'MENTORSHIP' => [
        'title' => 'Mentorship Program',
        'price' => 10000,
        'icon' => 'fa-user-graduate',
        'features' => [
            'Personalised 1-on-1 growth guidance',
            'Expert strategic insights',
            'Mentors who\'ve cracked NEET',
            'Regular progress tracking',
            'Study planning assistance'
        ]
    ]
];

$counselling_paths = [
    'MBBS' => [
        'title' => 'MBBS Counselling',
        'subtitle' => 'Medical',
        'icon' => 'doctor.png',
        'bg_color' => 'bg-soft-pink',
        'description' => 'Complete counselling support for Govt. MBBS, Semi-govt, Private and Deemed',
        'plans' => [
            [
                'name' => 'Government MBBS',
                'price' => '4999',
                'icon' => 'fa-graduation-cap'
            ],
            [
                'name' => 'Semi-Government',
                'price' => '9999',
                'icon' => 'fa-users'
            ],
            [
                'name' => 'Private/Deemed',
                'price' => '24999',
                'icon' => 'fa-university'
            ]
        ]
    ],
    'BDS' => [
        'title' => 'BDS Counselling',
        'subtitle' => 'Dental',
        'icon' => 'dental.png',
        'bg_color' => 'bg-soft-blue',
        'description' => 'Complete counselling support for Govt. BDS, Semi-govt, Private and Deemed',
        'plans' => [
            [
                'name' => 'Government BDS',
                'price' => '4999',
                'icon' => 'fa-tooth'
            ],
            [
                'name' => 'Semi-Government',
                'price' => '9999',
                'icon' => 'fa-users'
            ],
            [
                'name' => 'Private/Deemed',
                'price' => '24999',
                'icon' => 'fa-university'
            ]
        ]
    ],
    'AYUSH' => [
        'title' => 'AYUSH Counselling',
        'subtitle' => 'Ayu, Homeo, Unani',
        'icon' => 'ayush.png',
        'bg_color' => 'bg-soft-green',
        'description' => 'Complete counselling support for Govt. AYUSH, Semi-govt, Private and Deemed (Ayurveda, Unani, Homeopathy)',
        'plans' => [
            [
                'name' => 'Government',
                'price' => '4999',
                'icon' => 'fa-leaf'
            ],
            [
                'name' => 'Semi-Government',
                'price' => '9999',
                'icon' => 'fa-users'
            ],
            [
                'name' => 'Private/Deemed',
                'price' => '24999',
                'icon' => 'fa-university'
            ]
        ]
    ],
    'BVSC' => [
        'title' => 'BVSC & AH Counselling',
        'subtitle' => 'Veterinary',
        'icon' => 'veterinary.png',
        'bg_color' => 'bg-soft-yellow',
        'description' => 'Complete counselling support for Govt. Veterinary, Semi-govt, Private and Deemed',
        'plans' => [
            [
                'name' => 'Government',
                'price' => '4999',
                'icon' => 'fa-paw'
            ],
            [
                'name' => 'Semi-Government',
                'price' => '9999',
                'icon' => 'fa-users'
            ],
            [
                'name' => 'Private/Deemed',
                'price' => '24999',
                'icon' => 'fa-university'
            ]
        ]
    ],
    'CUET' => [
        'title' => 'CUET Counselling',
        'subtitle' => 'Central Universities',
        'icon' => 'university.png',
        'bg_color' => 'bg-soft-pink',
        'description' => 'For Central Universities and ICAR Counselling',
        'plans' => [
            [
                'name' => 'ICAR Counselling',
                'price' => '4999',
                'icon' => 'fa-seedling'
            ],
            [
                'name' => 'CUET Counselling',
                'price' => '4999',
                'icon' => 'fa-university'
            ],
            [
                'name' => 'CUET + CUET',
                'price' => '9999',
                'icon' => 'fa-plus-circle'
            ]
        ]
    ]
];

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service'])) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = 'counselling';
        $_SESSION['selected_plan'] = $_POST['plan_type'];
        header("Location: index.php?page=login");
        exit();
    }
    
    $plan_type = $_POST['plan_type'];
    
    if (!array_key_exists($plan_type, $plans)) {
        $error = "Invalid plan selected";
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        try {
            // Begin transaction
            $conn->beginTransaction();
            
            // Create new order
            $stmt = $conn->prepare("
                INSERT INTO orders (user_id, plan_type, amount, status, payment_status) 
                VALUES (:user_id, :plan_type, :amount, 'pending', 'pending')
            ");
            
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':plan_type' => $plan_type,
                ':amount' => $plans[$plan_type]['price']
            ]);
            
            $order_id = $conn->lastInsertId();
            
            // Log activity
            $stmt = $conn->prepare("
                INSERT INTO user_activity_log (user_id, action, description, ip_address) 
                VALUES (:user_id, :action, :description, :ip)
            ");
            
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':action' => 'create_order',
                ':description' => "Created order for {$plan_type} plan",
                ':ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to payment page
            header("Location: index.php?page=payment&order_id=" . $order_id);
            exit();
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            $error = "Failed to create order. Please try again later.";
        }
    }
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-3">Choose Your Success Path</h1>
        <p class="lead mb-0">Select the plan that best fits your needs and start your journey towards your dream medical college.</p>
    </div>
</section>

<!-- Counselling Paths Section -->
<section class="counselling-paths py-5">
    <div class="container">
        <h2 class="text-center display-4 mb-5">Choose Your Counselling Path</h2>
        <div class="row g-4 justify-content-center">
            <!-- MBBS Card -->
            <div class="col-md-6 col-lg-4">
                <div class="path-card bg-soft-pink" data-path="MBBS">
                    <div class="card-content">
                        <div class="icon-wrapper">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="text-content">
                            <h3>MBBS Counselling</h3>
                            <p>Medical</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BDS Card -->
            <div class="col-md-6 col-lg-4">
                <div class="path-card bg-soft-blue" data-path="BDS">
                    <div class="card-content">
                        <div class="icon-wrapper">
                            <i class="fas fa-tooth"></i>
                        </div>
                        <div class="text-content">
                            <h3>BDS Counselling</h3>
                            <p>Dental</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AYUSH Card -->
            <div class="col-md-6 col-lg-4">
                <div class="path-card bg-soft-green" data-path="AYUSH">
                    <div class="card-content">
                        <div class="icon-wrapper">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="text-content">
                            <h3>AYUSH Counselling</h3>
                            <p>Ayu, Homeo, Unani</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BVSC Card -->
            <div class="col-md-6 col-lg-4">
                <div class="path-card bg-soft-yellow" data-path="BVSC">
                    <div class="card-content">
                        <div class="icon-wrapper">
                            <i class="fas fa-paw"></i>
                        </div>
                        <div class="text-content">
                            <h3>BVSC & AH Counselling</h3>
                            <p>Veterinary</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CUET Card -->
            <div class="col-md-6 col-lg-4">
                <div class="path-card bg-soft-pink" data-path="CUET">
                    <div class="card-content">
                        <div class="icon-wrapper">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="text-content">
                            <h3>CUET Counselling</h3>
                            <p>Central Universities</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Sections -->
<?php foreach ($counselling_paths as $key => $path): ?>
<section class="pricing-section py-5 d-none" id="<?php echo $key; ?>-plans">
    <div class="container">
        <h2 class="text-center mb-3"><?php echo $path['title']; ?></h2>
        <p class="text-center text-muted mb-5"><?php echo $path['description']; ?></p>
        <div class="row g-4 justify-content-center">
            <?php foreach ($path['plans'] as $plan): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <i class="fas <?php echo $plan['icon']; ?> fa-2x mb-3"></i>
                            <h3><?php echo $plan['name']; ?></h3>
                            <div class="price">
                                <span class="currency">₹</span>
                                <span class="amount"><?php echo $plan['price']; ?></span>/-
                            </div>
                        </div>
                        <div class="pricing-body">
                            <a href="https://wa.me/7340409636?text=I'm interested in <?php echo urlencode($path['title'] . ' - ' . $plan['name']); ?> plan" 
                               class="btn btn-whatsapp w-100" target="_blank">
                                <i class="fab fa-whatsapp me-2"></i>Connect on WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>


<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">Why Choose Our Services?</h2>
                <p class="lead text-muted">We provide comprehensive support to ensure your success in the medical college admission process.</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="icon-circle bg-primary text-white mb-3">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4>Expert Guidance</h4>
                    <p class="text-muted">Get personalized guidance from experienced mentors who understand the NEET journey.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="icon-circle bg-primary text-white mb-3">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h4>Proven Track Record</h4>
                    <p class="text-muted">Successfully guided 1500+ students to their dream medical colleges.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="icon-circle bg-primary text-white mb-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h4>24/7 Support</h4>
                    <p class="text-muted">Round-the-clock assistance for all your queries and concerns.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I choose the right plan?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Choose based on your target colleges. Government Plan for government colleges, Semi-Government Plan for both government and semi-government colleges, and Private Plan for comprehensive coverage including private institutions.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What payment methods do you accept?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major payment methods including UPI, credit/debit cards, and net banking. All payments are processed securely.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I upgrade my plan later?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you can upgrade to a higher plan at any time. You'll only need to pay the difference in amount.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(45deg, #1a75ff, #0052cc);
    min-height: 50vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 0;
    color: #fff;
    position: relative;
    margin-bottom: 0;
}

.hero-section::after {
    content: '';
    position: absolute;
    bottom: -50px;
    left: 0;
    right: 0;
    height: 100px;
    background: #fff;
    clip-path: polygon(0 0, 100% 50%, 100% 100%, 0% 100%);
    z-index: 1;
}

.hero-section .container {
    position: relative;
    z-index: 2;
}

.hero-section h1 {
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.hero-section .lead {
    font-size: 1.25rem;
    max-width: 800px;
    margin: 0 auto;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .hero-section {
        min-height: 40vh;
        padding: 2rem 0;
    }

    .hero-section h1 {
        font-size: 2rem;
    }

    .hero-section .lead {
        font-size: 1.1rem;
    }
}

.path-card {
    border-radius: 15px;
    padding: 2rem;
    height: 100%;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.path-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.card-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.5);
}

.icon-wrapper i {
    font-size: 24px;
    color: #333;
}

.text-content h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 600;
}

.text-content p {
    color: #666;
    margin: 0;
    font-size: 0.95rem;
}

.bg-soft-pink {
    background-color: #FFE6E6;
}

.bg-soft-blue {
    background-color: #E6F0FF;
}

.bg-soft-green {
    background-color: #E6FFE6;
}

.bg-soft-yellow {
    background-color: #FFFFF0;
}

.icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hover-lift {
    transition: transform 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
}

/* Counselling Paths */
.counselling-paths {
    background: #fff;
}

.path-card {
    border-radius: 15px;
    padding: 2rem;
    height: 100%;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.path-card:hover {
    transform: translateY(-5px);
}

.bg-soft-pink {
    background-color: #FFE6E6;
}

.bg-soft-blue {
    background-color: #E6F0FF;
}

.bg-soft-green {
    background-color: #E6FFE6;
}

.bg-soft-yellow {
    background-color: #FFFFF0;
}

.card-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
}

.path-icon {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.text-content h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: #333;
}

/* Plans Overview */
.plans-overview {
    position: relative;
    overflow: hidden;
}

.plans-list {
    margin-top: 2rem;
}

.plan-item {
    padding: 1rem;
    border-radius: 10px;
    transition: background-color 0.3s ease;
}

.plan-item:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.plan-icon {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

@media (max-width: 768px) {
    .path-card {
        padding: 1.5rem;
    }

    .icon-wrapper {
        width: 50px;
        height: 50px;
    }

    .text-content h3 {
        font-size: 1.1rem;
    }
}

/* Pricing Cards */
.pricing-section {
    background: #f8f9fa;
}

.pricing-card {
    background: #fff;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    height: 100%;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.pricing-header {
    margin-bottom: 2rem;
}

.pricing-header i {
    color: #0d6efd;
}

.pricing-header h3 {
    margin: 1rem 0;
    font-size: 1.25rem;
    color: #333;
}

.price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #0d6efd;
    margin-bottom: 1rem;
}

.price .currency {
    font-size: 1.5rem;
    font-weight: 500;
    vertical-align: super;
}

.btn-whatsapp {
    background: #25D366;
    color: #fff;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-whatsapp:hover {
    background: #128C7E;
    color: #fff;
    transform: translateY(-2px);
}

/* WhatsApp Floating Action Button */
.whatsapp-fab {
    position: fixed;
    bottom: 30px;
    right: 20px;
    width: 50px;
    height: 50px;
    background: #25D366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    box-shadow: 0 2px 10px rgba(37, 211, 102, 0.3);
    transition: all 0.3s ease;
    z-index: 1000;
}

.whatsapp-fab:hover {
    background: #128C7E;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(37, 211, 102, 0.4);
}

.whatsapp-fab i {
    font-size: 28px;
}

@media (max-width: 768px) {
    .whatsapp-fab {
        width: 45px;
        height: 45px;
        right: 15px;
        bottom: 25px;
    }
    
    .whatsapp-fab i {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pathCards = document.querySelectorAll('.path-card');
    const pricingSections = document.querySelectorAll('.pricing-section');

    pathCards.forEach(card => {
        card.addEventListener('click', function() {
            const pathId = this.dataset.path;
            
            // Hide all pricing sections
            pricingSections.forEach(section => {
                section.classList.add('d-none');
            });
            
            // Show selected pricing section
            const selectedSection = document.getElementById(`${pathId}-plans`);
            if (selectedSection) {
                selectedSection.classList.remove('d-none');
                selectedSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
</script> 

<!-- Add this right before closing body tag -->
<a href="https://wa.me/7340409636" class="whatsapp-fab" target="_blank">
    <i class="fab fa-whatsapp"></i>
</a> 