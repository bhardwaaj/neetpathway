<?php
$stats = [
    [
        'icon' => 'fa-graduation-cap',
        'number' => '1500+',
        'text' => 'Students Counselled'
    ],
    [
        'icon' => 'fa-users',
        'number' => '50+',
        'text' => 'Expert Mentors'
    ],
    [
        'icon' => 'fa-user-md',
        'number' => '10+',
        'text' => 'Expert Counsellors'
    ],
    [
        'icon' => 'fa-star',
        'number' => '4.5/5',
        'text' => 'Student Ratings'
    ]
];

$journey = [
    2020 => [
        'title' => 'Foundation Year',
        'points' => [
            'Mentored 50+ dedicated students',
            'Established personalized mentoring approach',
            'One-on-one guidance sessions',
            'Focus on NEET fundamentals'
        ]
    ],
    2021 => [
        'title' => 'Team Growth',
        'points' => [
            'Added 5-member team for subject-wise doubt solving',
            'Introduced 24/7 doubt support system',
            'Structured study materials',
            'Performance tracking implementation'
        ]
    ],
    2022 => [
        'title' => 'Service Enhancement',
        'points' => [
            'Launched dedicated counselling for medical college admission',
            'Comprehensive documentation assistance',
            'Strategic planning support',
            'Expert college guidance'
        ]
    ],
    2023 => [
        'title' => 'Integrated Success',
        'points' => [
            'Empowered 150+ students with integrated mentorship',
            'Advanced study techniques',
            'Comprehensive support system',
            'Enhanced counselling services'
        ]
    ],
    2024 => [
        'title' => 'Scaling Impact',
        'points' => [
            'Assisted 500+ students with scalable approach',
            'Launched comprehensive digital platform',
            'Enhanced counselling services',
            'Expanded expert mentor network'
        ]
    ]
];
?>

<!-- Hero Section -->
<section class="hero-section">
    <div id="particles-js"></div>
    <div class="container hero-content">
        <div class="text-center mb-5">
            <img src="images/logo.PNG" style="border-radius: 45px;" alt="NEET Pathway" class="hero-logo mb-4">
            <h1 class="display-4 mb-3" style="color: #fff;">NEET Pathway</h1>
            <h2 class="h3 mb-4">Every Step Matters</h2>
            <div id="typewriter-text" class="lead typewriter-text mb-5"></div>
            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="index.php?page=counselling" class="btn btn-light btn-lg">Get Counselling</a>
                <a href="index.php?page=mentorship" class="btn btn-outline-light btn-lg">Join Mentorship</a>
            </div>
        </div>
    </div>
</section>

<!-- Scripts for Particles.js -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="assets/js/particles-config.js"></script>

<!-- Counselling Services Section -->
<section class="services-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Our Counselling Services</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="fas fa-user-md fa-2x"></i>
                    </div>
                    <h3 class="h5 mt-4">Personalized One-on-One Expert Counselling</h3>
                    <p class="text-muted">Get individualized attention and guidance from our experienced counsellors.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="fas fa-rupee-sign fa-2x"></i>
                    </div>
                    <h3 class="h5 mt-4">Affordable & Transparent Pricing</h3>
                    <p class="text-muted">Clear fee structure with no hidden charges for peace of mind.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <h3 class="h5 mt-4">Registration & Form Filling Support</h3>
                    <p class="text-muted">Complete assistance in filling and submitting your applications correctly.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="fas fa-university fa-2x"></i>
                    </div>
                    <h3 class="h5 mt-4">AIQ + State Counselling Assistance</h3>
                    <p class="text-muted">Expert guidance for both All India Quota and State-level counselling processes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="fas fa-award fa-2x"></i>
                    </div>
                    <h3 class="h5 mt-4">Documentation & Scholarship Guidance</h3>
                    <p class="text-muted">Support in preparing and organizing all required documents and scholarship applications.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h3 class="h5 mt-4">Deep Analysis & Insights</h3>
                    <p class="text-muted">Detailed analysis of cutoff trends, seat matrix, bond terms, and fee structures.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
            <a href="index.php?page=counselling" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar-check me-2"></i>Book Your Counselling Session
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose NEET Pathway?</h2>
        <div class="row">
            <?php foreach ($stats as $stat): ?>
                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <i class="fas <?php echo $stat['icon']; ?>"></i>
                        <h3 class="h2 mb-3"><?php echo $stat['number']; ?></h3>
                        <p class="text-muted mb-0"><?php echo $stat['text']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Journey Section -->
<section class="journey-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Our Journey</h2>
        <div class="timeline">
            <?php foreach ($journey as $year => $details): ?>
                <div class="timeline-item">
                    <div class="timeline-year">
                        <span class="year"><?php echo $year; ?></span>
                        <h3 class="title"><?php echo $details['title']; ?></h3>
                    </div>
                    <div class="timeline-content">
                        <ul class="timeline-points">
                            <?php foreach ($details['points'] as $point): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo $point; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 text-center text-md-start">
                <h2 class="mb-3">Ready to Start Your Medical Journey?</h2>
                <p class="lead mb-4">Get expert guidance and personalized mentorship to achieve your NEET goals.</p>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <a href="https://wa.me/7340409636" class="btn btn-primary btn-lg" target="_blank">
                    <i class="fab fa-whatsapp me-2"></i> Connect Now
                </a>
            </div>
        </div>
    </div>
</section> 