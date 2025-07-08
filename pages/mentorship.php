<?php
$features = [
    [
        'icon' => 'fa-user',
        'title' => 'Personalised 1:1 Guidance',
        'description' => 'Get dedicated attention from experienced mentors who understand your unique needs'
    ],
    [
        'icon' => 'fa-graduation-cap',
        'title' => 'Expert Insights',
        'description' => 'Learn from mentors who have successfully achieved your dream'
    ],
    [
        'icon' => 'fa-chart-line',
        'title' => 'Strategic Direction',
        'description' => 'Get a customized roadmap to achieve your NEET goals'
    ],
    [
        'icon' => 'fa-star',
        'title' => 'Proven Track Record',
        'description' => 'Join a program that has helped 500+ students succeed'
    ]
];

$benefits = [
    'Regular progress tracking and feedback',
    'Subject-wise doubt solving sessions',
    'Mock test analysis and improvement strategies',
    'Study schedule optimization',
    'Stress management and motivation',
    'Exam strategy planning'
];
?>

<!-- Hero Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h1 class="mb-4">NEET Mentorship Program</h1>
        <p class="lead mb-0">Unlock your full potential with expert insights</p>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <?php foreach ($features as $feature): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="fas <?php echo $feature['icon']; ?> fa-3x text-primary mb-3"></i>
                            <h3 class="h4 mb-3"><?php echo $feature['title']; ?></h3>
                            <p class="text-muted mb-0"><?php echo $feature['description']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Mentorship Plan Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">What You'll Get</h2>
        <div class="row">
            <!-- Plan Details -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h4 mb-4">Mentorship Plan</h3>
                        <div class="text-center mb-4">
                            <h4 class="text-primary display-4">₹10,000/-</h4>
                            <p class="text-muted">Comprehensive 1-year mentorship program</p>
                        </div>
                        <hr>
                        <ul class="list-unstyled">
                            <?php foreach ($benefits as $benefit): ?>
                                <li class="mb-3">
                                    <i class="fas fa-star text-primary me-2"></i>
                                    <?php echo $benefit; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="https://wa.me/7340409636" class="btn btn-primary btn-lg w-100" target="_blank">
                                Join Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Why Choose Us -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 bg-primary text-white">
                    <div class="card-body">
                        <h3 class="h4 mb-4">Why Choose Our Mentorship?</h3>
                        <p class="lead">Our mentorship program is designed by successful medical students who understand
                            the challenges of NEET preparation. We provide personalized guidance that goes
                            beyond just academics - we help you develop the right mindset, study techniques,
                            and strategies to crack NEET.</p>
                        
                        <h4 class="h5 mt-4 mb-3">Our Success Metrics:</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                50+ Expert Mentors
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                500+ Students Mentored
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                4.5/5 Student Rating
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Student Success Stories</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-quote-left text-primary fa-2x"></i>
                        </div>
                        <p class="mb-4">The mentorship program helped me understand my strengths and weaknesses. 
                            My mentor's guidance was crucial in improving my NEET score.</p>
                        <div class="d-flex align-items-center">
                            <div class="ms-3">
                                <h5 class="mb-0">Priya Sharma</h5>
                                <small class="text-muted">NEET 2023 Qualifier</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-quote-left text-primary fa-2x"></i>
                        </div>
                        <p class="mb-4">The personalized study plan and regular mock tests helped me stay on track. 
                            The mentors are very supportive and always available.</p>
                        <div class="d-flex align-items-center">
                            <div class="ms-3">
                                <h5 class="mb-0">Rahul Patel</h5>
                                <small class="text-muted">NEET 2023 Qualifier</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-quote-left text-primary fa-2x"></i>
                        </div>
                        <p class="mb-4">The stress management sessions and motivation from mentors helped me stay focused. 
                            I'm grateful for their guidance.</p>
                        <div class="d-flex align-items-center">
                            <div class="ms-3">
                                <h5 class="mb-0">Anjali Singh</h5>
                                <small class="text-muted">NEET 2023 Qualifier</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Begin Your NEET Journey?</h2>
        <p class="lead mb-4">Join our mentorship program and get the guidance you need to succeed</p>
        <a href="https://wa.me/7340409636" class="btn btn-primary btn-lg" target="_blank">
            <i class="fab fa-whatsapp me-2"></i> Connect With a Mentor
        </a>
    </div>
</section> 