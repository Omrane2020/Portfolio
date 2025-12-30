// Mobile menu toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const navLinks = document.querySelector('.nav-links');

mobileMenuBtn.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    mobileMenuBtn.innerHTML = navLinks.classList.contains('active')
        ? '<i class="fas fa-times"></i>'
        : '<i class="fas fa-bars"></i>';
});

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('active');
        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        const targetId = this.getAttribute('href');
        if (targetId === '#') return;

        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Form submission
const contactForm = document.getElementById('contactForm');
const submitBtn = document.getElementById('submitBtn');
const formMessages = document.getElementById('formMessages');

contactForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    // Désactiver le bouton pendant l'envoi
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';

    // Vider les messages précédents
    formMessages.innerHTML = '';

    // Collecter les données du formulaire
    const formData = new FormData(this);

    try {
        // Envoyer les données via AJAX
        const response = await fetch('index.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Afficher le message de succès
            formMessages.innerHTML = `
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> ${result.message}
                </div>
            `;

            // Réinitialiser le formulaire
            contactForm.reset();
        } else {
            // Afficher les erreurs
            let errorHtml = '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">';

            if (result.errors) {
                errorHtml += '<ul style="margin: 0; padding-left: 20px;">';
                result.errors.forEach(error => {
                    errorHtml += `<li>${error}</li>`;
                });
                errorHtml += '</ul>';
            } else if (result.message) {
                errorHtml += `<p><i class="fas fa-exclamation-circle"></i> ${result.message}</p>`;
            }

            errorHtml += '</div>';
            formMessages.innerHTML = errorHtml;
        }
    } catch (error) {
        // Erreur réseau
        formMessages.innerHTML = `
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i> Erreur réseau. Veuillez vérifier votre connexion et réessayer.
            </div>
        `;
        console.error('Erreur:', error);
    } finally {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer le message';
    }

    // Reset form
    contactForm.reset();
});

// Sticky header
window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if (window.scrollY > 100) {
        header.style.boxShadow = 'var(--shadow-md)';
    } else {
        header.style.boxShadow = 'var(--shadow-sm)';
    }
});

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.querySelectorAll('.timeline-item, .project-card, .skill-category').forEach(element => {
    element.style.opacity = '0';
    element.style.transform = 'translateY(20px)';
    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(element);
});

// Fonction pour ajouter une photo depuis le téléphone
function addPhotoFromDevice() {
    // Créer un input de type file
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';

    input.onchange = function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.querySelector('.profile-image img');
                img.src = e.target.result;

                // Sauvegarder dans le localStorage pour la prochaine fois
                localStorage.setItem('profilePhoto', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    };

    // Ouvrir le sélecteur de fichiers
    if (window.AndroidInterface) {
        // Si on est sur Android, utiliser l'interface native
        window.AndroidInterface.openImagePicker();
    } else {
        // Sinon, utiliser le sélecteur de fichiers standard
        input.click();
    }
}

// Charger la photo depuis le localStorage au chargement
window.addEventListener('DOMContentLoaded', () => {
    const savedPhoto = localStorage.getItem('profilePhoto');
    if (savedPhoto) {
        const img = document.querySelector('.profile-image img');
        img.src = savedPhoto;
    }

    /* Ajouter un bouton pour changer la photo (optionnel)
    const heroImage = document.querySelector('.hero-image');
    const changePhotoBtn = document.createElement('button');
    changePhotoBtn.innerHTML = '<i class="fas fa-camera"></i> Changer photo';
    changePhotoBtn.className = 'btn';
    changePhotoBtn.style.marginTop = '20px';
    changePhotoBtn.style.background = 'var(--accent-color)';
    changePhotoBtn.onclick = addPhotoFromDevice;
    
    heroImage.appendChild(changePhotoBtn); */
});