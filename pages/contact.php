<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <h1 class="text-center mb-5">Contact Us</h1>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h4 mb-4">Get in Touch</h2>
                        <div class="mb-4">
                            <h3 class="h5 mb-3">WhatsApp</h3>
                            <a href="https://wa.me/7340409636" class="text-decoration-none d-flex align-items-center" target="_blank">
                                <i class="fab fa-whatsapp fa-2x text-success me-2"></i>
                                <span>+91 7340409636</span>
                            </a>
                        </div>
                        <div class="mb-4">
                            <h3 class="h5 mb-3">Email</h3>
                            <a href="mailto:pathwayneet@gmail.com" class="text-decoration-none d-flex align-items-center">
                                <i class="fas fa-envelope fa-2x text-primary me-2"></i>
                                <span>pathwayneet@gmail.com</span>
                            </a>
                        </div>
                        <div class="mt-5">
                            <h3 class="h5 mb-3">Quick Connect</h3>
                            <a href="https://wa.me/7340409636" class="btn btn-success btn-lg w-100 mb-3" target="_blank">
                                <i class="fab fa-whatsapp me-2"></i> Chat on WhatsApp
                            </a>
                            <a href="mailto:pathwayneet@gmail.com" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-envelope me-2"></i> Send Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h4 mb-4">Send us a Message</h2>
                        <form id="contactForm" action="/includes/contact_handler.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form JavaScript -->
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            form.reset();
            alert(data.message);
        } else {
            throw new Error(data.message || 'Something went wrong. Please try again.');
        }
    })
    .catch(error => {
        alert(error.message);
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Send Message';
    });
});
</script> 