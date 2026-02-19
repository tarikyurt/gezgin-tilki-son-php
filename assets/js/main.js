// Main JavaScript File

document.addEventListener('DOMContentLoaded', () => {
    console.log('Gezgin Tilki Website Loaded');

    // Premium Header - Scroll handler for glassmorphism transitions
    const header = document.querySelector('header');
    const isTransparentPage = header.classList.contains('transparent');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
            header.classList.remove('transparent');
        } else {
            header.classList.remove('scrolled');
            if (isTransparentPage) {
                header.classList.add('transparent');
            }
        }
    });

    // Active page highlighting
    const currentPath = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-links a:not(.btn-primary)').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || (currentPath === '' && href === 'index.php')) {
            link.classList.add('active');
        }
    });

    // Mobile Menu Toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');

    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            // Toggle icon between bars and times (close)
            const icon = mobileBtn.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Guest Selector Logic
    const guestSelectorBtn = document.querySelector('.guest-selector-btn');
    const guestDropdown = document.querySelector('.guest-dropdown');

    if (guestSelectorBtn && guestDropdown) {
        // Toggle Dropdown
        guestSelectorBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            guestDropdown.style.display = guestDropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!guestSelectorBtn.contains(e.target) && !guestDropdown.contains(e.target)) {
                guestDropdown.style.display = 'none';
            }
        });
    }

    // Make updateGuests globally available or attach to window if needed, 
    // but better to add event listeners to buttons dynamically if looking for cleaner code.
    // For simplicity with the onclick attributes in HTML:
    window.updateGuests = function (type, change) {
        const countSpan = document.getElementById(type + '-count');
        let count = parseInt(countSpan.textContent);

        count += change;
        if (count < 0) count = 0;
        // Min 1 adult usually required
        if (type === 'adult' && count < 1) count = 1;

        countSpan.textContent = count;
        updateGuestSummary();
    };

    function updateGuestSummary() {
        const adults = document.getElementById('adult-count').textContent;
        const children = document.getElementById('child-count').textContent;
        document.getElementById('guest-summary').textContent = `${adults} Yetişkin, ${children} Çocuk`;
    }

    // Date Range Picker (Flatpickr)
    const dateRangeInput = document.querySelector("#date-range");
    if (dateRangeInput) {
        flatpickr(dateRangeInput, {
            mode: "range",
            minDate: "today",
            dateFormat: "d.m.Y",
            locale: "tr",
            showMonths: 2, // Show 2 months for better range selection
            theme: "material_red", // Built-in theme usually needs separate CSS, will customize manually
            onClose: function (selectedDates, dateStr, instance) {
                // Optional: Do something when date is picked
            }
        });
    }

    // Destination Carousel (Owl Carousel)
    $('.destination-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 4000,
        autoplayHoverPause: true,
        navText: ["<i class='fa-solid fa-chevron-left'></i>", "<i class='fa-solid fa-chevron-right'></i>"],
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1000: {
                items: 3
            },
            1200: {
                items: 4
            }
        }
    });

    // Hero Slider
    const slides = document.querySelectorAll('.hero-bg');
    let currentSlide = 0;

    if (slides.length > 0) {
        setInterval(() => {
            slides[currentSlide].style.opacity = '0';
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].style.opacity = '1';
        }, 5000); // Change image every 5 seconds
    }
});
