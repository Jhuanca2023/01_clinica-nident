// Deshabilitar botón de envío mientras se procesa (reCAPTCHA v3 se maneja en la blade)
document.addEventListener('DOMContentLoaded', function () {
    const contactForm = document.getElementById('contactForm');

    if (contactForm) {
        contactForm.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enviando...';
            }
        });
    }
});
