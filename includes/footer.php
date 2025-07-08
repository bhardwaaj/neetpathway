    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-4" style="color:wheat">About NEET Pathway</h5>
                    <p class="mb-4">Your one-stop solution for expert guidance and personalized mentorship for NEET aspirants. From planning to securing medical college admission, we support every step of your journey.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/neetpathway/" target="_blank" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2">
                    <h5 class="mb-4" style="color:wheat">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="index.php?page=about" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="index.php?page=counselling" class="text-white text-decoration-none">Counselling</a></li>
                        <li class="mb-2"><a href="index.php?page=mentorship" class="text-white text-decoration-none">Mentorship</a></li>
                        <li class="mb-2"><a href="index.php?page=contact" class="text-white text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3">
                    <h5 class="mb-4" style="color:wheat">Our Services</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check me-2"></i>Government College Counselling</li>
                        <li class="mb-2"><i class="fas fa-check me-2"></i>Private College Admission</li>
                        <li class="mb-2"><i class="fas fa-check me-2"></i>NEET Mentorship Program</li>
                        <li class="mb-2"><i class="fas fa-check me-2"></i>Documentation Support</li>
                        <li class="mb-2"><i class="fas fa-check me-2"></i>Career Guidance</li>
                    </ul>
                </div>
                
                <div class="col-lg-3">
                    <h5 class="mb-4" style="color:wheat">Contact Info</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:7340409636" class="text-white text-decoration-none">+91 7340409636</a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:info@neetpathway.com" class="text-white text-decoration-none">info@neetpathway.com</a>
                        </li>
                        <li class="mb-3">
                            <i class="fab fa-whatsapp me-2"></i>
                            <a href="https://wa.me/7340409636" class="text-white text-decoration-none">WhatsApp Support</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> NEET Pathway. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <a href="#" class="text-white text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-white text-decoration-none me-3">Terms of Service</a>
                    <a href="#" class="text-white text-decoration-none">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->

    <!-- TypewriterJS -->
    <script src="https://unpkg.com/typewriter-effect@latest/dist/core.js"></script>
    <script>
        // Back to Top Button
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Initialize Typewriter Effect
        window.addEventListener('load', function() {
            const typewriterElement = document.getElementById('typewriter-text');
            if (typewriterElement) {
                const typewriter = new Typewriter(typewriterElement, {
                    loop: true,
                    delay: 50,
                    deleteSpeed: 30
                });

                typewriter
                    .typeString('Expert Guidance')
                    .pauseFor(2000)
                    .deleteAll()
                    .typeString('Personalized Mentorship')
                    .pauseFor(2000)
                    .deleteAll()
                    .typeString('NEET Success')
                    .pauseFor(2000)
                    .deleteAll()
                    .typeString('Your Medical Journey Starts Here')
                    .pauseFor(2000)
                    .start();
            }
        });
    </script>
</body>
</html> 