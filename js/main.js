document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pledgeForm');
    const messageDiv = document.getElementById('message');
    // const pledgeCountSpan = document.getElementById('pledgeCount');
    const heroCountSpan = document.getElementById('heroCount');

    // Load pledge count on page load
    loadPledgeCount();

    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear any existing messages
        hideMessage();
        
        // Get form data
        const formData = new FormData(form);
        
        // Basic client-side validation
        if (!validateForm(formData)) {
            return;
        }
        
        // Disable submit button during submission
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
        
        // Submit form via AJAX
        fetch('process_pledge.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', 'Thank you for taking the pledge! Your commitment to better hiring practices has been recorded.');
                form.reset();
                loadPledgeCount(); // Refresh the count
            } else {
                showMessage('error', data.message || 'An error occurred while processing your pledge. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'A network error occurred. Please check your connection and try again.');
        })
        .finally(() => {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });

    function validateForm(formData) {
        const requiredFields = ['company_name', 'contact_name', 'email'];
        const errors = [];

        // Check required fields
        for (const field of requiredFields) {
            const value = formData.get(field);
            if (!value || value.trim() === '') {
                errors.push(`${getFieldLabel(field)} is required.`);
            }
        }

        // Validate email format
        const email = formData.get('email');
        if (email && !isValidEmail(email)) {
            errors.push('Please enter a valid email address.');
        }

        // Validate website URL if provided
        const website = formData.get('website');
        if (website && website.trim() !== '' && !isValidURL(website)) {
            errors.push('Please enter a valid website URL.');
        }

        // Check agreement checkbox
        const agree = formData.get('agree');
        if (!agree) {
            errors.push('You must agree to uphold these interviewing practices.');
        }

        // Check honeypot (spam protection)
        const honeypot = formData.get('honeypot');
        if (honeypot && honeypot.trim() !== '') {
            errors.push('Spam detected. Please try again.');
        }

        if (errors.length > 0) {
            showMessage('error', errors.join('<br>'));
            return false;
        }

        return true;
    }

    function getFieldLabel(fieldName) {
        const labels = {
            'company_name': 'Company Name',
            'contact_name': 'Your Name',
            'email': 'Email Address',
            'title': 'Job Title',
            'website': 'Company Website'
        };
        return labels[fieldName] || fieldName;
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidURL(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    function showMessage(type, message) {
        messageDiv.className = `mb-6 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'}`;
        messageDiv.innerHTML = message;
        messageDiv.classList.remove('hidden');
        
        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function hideMessage() {
        messageDiv.classList.add('hidden');
    }

    function loadPledgeCount() {
        fetch('get_pledge_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = data.data.count;
                    // pledgeCountSpan.textContent = count;
                    if (heroCountSpan) {
                        heroCountSpan.textContent = count;
                    }
                } else {
                    // pledgeCountSpan.textContent = '0';
                    if (heroCountSpan) {
                        heroCountSpan.textContent = '0';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading pledge count:', error);
                // pledgeCountSpan.textContent = '0';
                if (heroCountSpan) {
                    heroCountSpan.textContent = '0';
                }
            });
    }

    // Add smooth scrolling for any anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add form field focus effects
    const formInputs = form.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});
