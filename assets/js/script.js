// script.js - Client-side script for MechaForge Engineering

let currentBgIndex = 0;
let slideInterval;

function startSlideshow() {
    const images = document.querySelectorAll('.hero-bg-image');
    if (images.length <= 1) return;
    
    clearInterval(slideInterval);
    slideInterval = setInterval(() => {
        images[currentBgIndex].classList.remove('active');
        currentBgIndex = (currentBgIndex + 1) % images.length;
        images[currentBgIndex].classList.add('active');
    }, 5000);
}

// Modal handling
const adminBtn = document.getElementById('admin-btn');
const adminModal = document.getElementById('admin-modal');
const closeModal = document.querySelector('.close-modal');

if (adminBtn && adminModal) {
    adminBtn.addEventListener('click', () => {
        adminModal.style.display = 'flex';
    });
}

if (closeModal && adminModal) {
    closeModal.addEventListener('click', () => {
        adminModal.style.display = 'none';
    });
}

window.addEventListener('click', (e) => {
    if (adminModal && e.target === adminModal) {
        adminModal.style.display = 'none';
    }
});

// Auto-open modal if there is a login error
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('login_error') && adminModal) {
    adminModal.style.display = 'flex';
}

// WhatsApp Contact Form Logic
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const name = document.getElementById('waName').value;
        const email = document.getElementById('waEmail').value;
        const message = document.getElementById('waMessage').value;
        
        // Grab the dynamically loaded phone number
        const rawPhone = (typeof siteSettings !== 'undefined' && siteSettings.phone) ? siteSettings.phone : '+94 77 123 4567';
        const formattedPhone = rawPhone.replace(/[^\d+]/g, '');
        
        const text = `Hello MechaForge!%0A%0A*Name:* ${name}%0A*Email:* ${email}%0A%0A*Message:*%0A${message}`;
        const whatsappUrl = `https://wa.me/${formattedPhone}?text=${text}`;
        
        window.open(whatsappUrl, '_blank');
    });
}

// Initialize slideshow on page load
document.addEventListener('DOMContentLoaded', () => {
    startSlideshow();
});
