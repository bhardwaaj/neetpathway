<?php
$team_members = [
    [
        'name' => 'Dr. Manoj Bugaliya',
        'role' => 'Founder & CEO',
        'image' => 'manoj.JPG',
        'description' => 'Medical student by profession and a social worker by passion. Created NEET Pathway to solve the confusion and chaos students face during counselling.'
    ],
    [
        'name' => 'Dr. Bharat Tailor',
        'role' => 'Senior Medical Advisor - AIIMS Bhatinda',
        'image' => 'bhart.WEBP',
        'description' => 'Expert in medical education from AIIMS Bhatinda. Provides strategic guidance on AIIMS preparation and counselling process.'
    ],
    [
        'name' => 'Dr. Aashik Choudhary',
        'role' => 'Academic Mentor - SNMC Jodhpur',
        'image' => 'Aashik.JPG',
        'description' => 'Experienced mentor from SNMC Jodhpur with expertise in guiding students through medical entrance preparation and counselling.'
    ],
    [
        'name' => 'Dr. Rohit Sharma',
        'role' => 'Medical Counsellor - MGMC Jaipur',
        'image' => 'Rohit.JPG',
        'description' => 'Specializes in career counselling and medical college selection strategies. Brings valuable insights from MGMC Jaipur.'
    ],
    [
        'name' => 'Dr. Kailash Choudhary',
        'role' => 'Strategic Advisor - AIIMS Jodhpur',
        'image' => 'Kailash.jpeg',
        'description' => 'Expert in medical education from AIIMS Jodhpur. Provides guidance on preparation strategies and college selection.'
    ],
    [
        'name' => 'Dr. Chaman Jain',
        'role' => 'Academic Consultant - MGMC Jaipur',
        'image' => 'Chaman.JPG',
        'description' => 'Experienced medical educator from MGMC Jaipur. Specializes in helping students navigate their medical education journey.'
    ]
];

$milestones = [
    [
        'year' => '2020',
        'achievement' => 'Mentored 50+ students',
        'icon' => 'fa-users'
    ],
    [
        'year' => '2021',
        'achievement' => 'Added 5-member team for subject-wise doubt solving',
        'icon' => 'fa-chalkboard-teacher'
    ],
    [
        'year' => '2022',
        'achievement' => 'Launched dedicated counselling for medical college admission',
        'icon' => 'fa-graduation-cap'
    ],
    [
        'year' => '2023',
        'achievement' => 'Empowered 150+ students with integrated mentorship',
        'icon' => 'fa-chart-line'
    ],
    [
        'year' => '2024',
        'achievement' => 'Assisted 500+ students with a scalable approach',
        'icon' => 'fa-rocket'
    ]
];
?>

<!-- Hero Section -->
<section class="about-hero py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 mb-4">About NEET Pathway</h1>
                <div class="typewriter-container mb-4">
                    <span class="lead">We are </span>
                    <span id="typewriter" class="lead"></span>
                </div>
                <p class="lead mb-4">Much like how parents guide a child's first step, and teachers shape life, NEET Pathway bridges your hard work to your dream medical college. We are the final support system that ensures you get the seat you deserve.</p>
                <div class="d-flex gap-3">
                    <a href="index.php?page=counselling" class="btn btn-light btn-lg">Get Counselling</a>
                    <a href="index.php?page=contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="images/about.png" style="width: 100%; height: 100%; border-radius: 20px;" alt="About NEET Pathway" class="img-fluid">
            </div>
        </div>
    </div>
</section>
<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-center mb-4 mb-md-0">
                <img src="images/manoj.jpg" alt="Dr. Manoj Bugaliya" class="rounded-circle founder-image mb-3" style="max-width: 250px; width: 100%;">
                <h3 class="h4 mb-1">Dr. Manoj Bugaliya</h3>
                <p class="text-muted">Founder & CEO</p>
            </div>
            <div class="col-md-8">
                <div class="founder-message">
                    <h2 class="mb-4">Founder's Message</h2>
                    <p class="lead mb-4">Hi, I'm Dr. Manoj Bugaliya, founder of NEET Pathway.</p>
                    <p class="mb-4">I, along with the dedicated NEET Pathway Team, started this platform to guide NEET aspirants like you through personalized counselling and continuous mentorship. Whether it's choosing the right medical college, navigating counselling rounds, or staying motivated throughout the year — we're with you at every step.</p>
                    <p class="mb-0">Let's make your medical journey easier, smarter, and more confident — together.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-bullseye fa-3x text-primary mb-4"></i>
                        <h3 class="h4 mb-3">Our Mission</h3>
                        <p class="mb-0">Every step you take on your NEET journey matters — we're here to guide each one.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-eye fa-3x text-primary mb-4"></i>
                        <h3 class="h4 mb-3">Our Vision</h3>
                        <p class="mb-0">To become the most trusted partner for NEET aspirants in their journey to medical college.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-heart fa-3x text-primary mb-4"></i>
                        <h3 class="h4 mb-3">Our Values</h3>
                        <p class="mb-0">Integrity, Excellence, Student Success, and Continuous Innovation drive everything we do.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Our Team</h2>
        <div class="row g-4">
            <?php foreach ($team_members as $member): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <img src="images/<?php echo $member['image']; ?>" style="width: 80%; height: 80%; border-radius: 20px; align-items: center; margin-left: 10%;" class="card-img-top" alt="<?php echo $member['name']; ?>">
                        <div class="card-body text-center p-4">
                            <h3 class="h5 mb-2"><?php echo $member['name']; ?></h3>
                            <p class="text-muted mb-3"><?php echo $member['role']; ?></p>
                            <p class="mb-0"><?php echo $member['description']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Milestones Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Our Journey</h2>
        <div class="row g-4">
            <?php foreach ($milestones as $milestone): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas <?php echo $milestone['icon']; ?> fa-2x text-primary mb-3"></i>
                            <h3 class="h5 mb-2"><?php echo $milestone['year']; ?></h3>
                            <p class="mb-0"><?php echo $milestone['achievement']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose NEET Pathway?</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-check-circle text-primary fa-2x me-3 mt-1"></i>
                    <div>
                        <h3 class="h5 mb-3">Expert Guidance</h3>
                        <p>Get personalized guidance from experienced mentors who understand the NEET journey.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-user-graduate text-primary fa-2x me-3 mt-1"></i>
                    <div>
                        <h3 class="h5 mb-3">Proven Track Record</h3>
                        <p>Successfully guided 1500+ students to their dream medical colleges.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-clock text-primary fa-2x me-3 mt-1"></i>
                    <div>
                        <h3 class="h5 mb-3">24/7 Support</h3>
                        <p>Round-the-clock assistance for all your queries and concerns.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-road text-primary fa-2x me-3 mt-1"></i>
                    <div>
                        <h3 class="h5 mb-3">Personalized Roadmap</h3>
                        <p>Custom strategies tailored to your strengths and goals.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-chart-bar text-primary fa-2x me-3 mt-1"></i>
                    <div>
                        <h3 class="h5 mb-3">Data-Driven Approach</h3>
                        <p>Make informed decisions based on historical data and trends.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-hands-helping text-primary fa-2x me-3 mt-1"></i>
                    <div>
                        <h3 class="h5 mb-3">Complete Support</h3>
                        <p>From counselling to admission, we're with you at every step.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start">
                <h2 class="mb-3">Ready to Start Your Medical Journey?</h2>
                <p class="lead mb-lg-0">Join NEET Pathway today and take the first step towards your dream medical college.</p>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <a href="index.php?page=register" class="btn btn-light btn-lg">Get Started Now</a>
            </div>
        </div>
    </div>
</section>

<style>
.typewriter-container {
    min-height: 60px;
    display: flex;
    align-items: center;
    gap: 8px;
}

#typewriter {
    display: inline-block;
}

.cursor {
    display: inline-block;
    width: 3px;
    background-color: #fff;
    margin-left: 2px;
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

.txt {
    border-right: 0.2rem solid #fff;
    animation: blink 0.7s infinite;
}
</style>

<script src="assets/js/typewriter.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typewriter = document.getElementById('typewriter');
    const words = [
        'Your Trusted Guide',
        'Expert Counsellors',
        'Medical Mentors',
        'Success Partners',
        'Dream Enablers'
    ];
    
    new TypeWriter(typewriter, words, 2000);
});
</script> 