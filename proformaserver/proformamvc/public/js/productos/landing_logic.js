/**
 * Logica de Interacción para Landing de Producto (Public Show)
 * Incluye: Swiper, Lightbox, Validacion Formulario, WhatsApp, Confetti, GSAP
 */

/**
 * Lightbox Logic (Swiper)
 */
var swiperLightbox = null;

function initLightboxSwiper() {
    if (swiperLightbox) return; // Ya existe

    swiperLightbox = new Swiper(".swiper-lightbox", {
        spaceBetween: 30,
        initialSlide: 0,
        zoom: true, // Habilitar zoom
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
            dynamicBullets: true,
        },
        keyboard: {
            enabled: true,
        },
        grabCursor: true,
    });
}

function openLightbox(index) {
    const modal = document.getElementById('lightboxModal');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden'; // Lock scroll

    // Initialize Swiper ONLY if opened (lazy init)
    if (!swiperLightbox) {
        initLightboxSwiper();
    }

    // Jump to specific slide
    swiperLightbox.slideTo(index, 0);
}

function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = ''; // Unlock scroll
}

// Background click logic (Careful with Swiper dragging)
document.querySelectorAll('.swiper-lightbox .swiper-slide').forEach(slide => {
    slide.addEventListener('click', function (e) {
        if (e.target === this) {
            closeLightbox();
        }
    });
});

// --- Gallery ---
var swiperThumbs = new Swiper(".mySwiper", {
    spaceBetween: 10,
    slidesPerView: 4.5,
    freeMode: true,
    watchSlidesProgress: true,
});

var swiperMain = new Swiper(".mySwiper2", {
    spaceBetween: 10,
    loop: true,
    autoplay: {
        delay: 3500,
        disableOnInteraction: false,
    },
    thumbs: {
        swiper: swiperThumbs,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
});

// --- Modal States ---
function openProformaModal() {
    const modal = document.getElementById('proformaModal');
    const content = modal.querySelector('.bg-white');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // GSAP Elastic Entry
    gsap.fromTo(content,
        { scale: 0.8, opacity: 0, y: 50 },
        { scale: 1, opacity: 1, y: 0, duration: 0.6, ease: "back.out(1.7)" }
    );

    playFeedbackSound('open');
    setTimeout(() => document.getElementById('clientName').focus(), 400);
}

function closeModal() {
    const modal = document.getElementById('proformaModal');
    const content = modal.querySelector('.bg-white');

    gsap.to(content, {
        scale: 0.8,
        opacity: 0,
        y: 50,
        duration: 0.3,
        ease: "power2.in",
        onComplete: () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            resetForm();
        }
    });
}

function resetForm() {
    const nameInput = document.getElementById('clientName');
    const docInput = document.getElementById('docNumber');

    nameInput.value = '';
    docInput.value = '';

    document.getElementById('nameError').classList.add('hidden');
    document.getElementById('name-check').classList.add('hidden');
    document.getElementById('name-error-icon').classList.add('hidden');

    document.getElementById('dniErrorText').classList.add('hidden');
    document.getElementById('dni-check').classList.add('hidden');
    document.getElementById('dni-error').classList.add('hidden');
    document.getElementById('dni-lottie-success').classList.add('hidden');

    document.getElementById('name-lottie-success').classList.add('hidden');

    resetInputStyle(nameInput);
    resetInputStyle(docInput);
}


// --- Validation Logic (Advanced) ---
function validateName(input, isSubmit = false) {
    const val = input.value;
    const errorMsg = document.getElementById('nameError');
    const checkIcon = document.getElementById('name-check');
    const errorIcon = document.getElementById('name-error-icon');
    const lottieSuccess = document.getElementById('name-lottie-success');

    // 1. Limpieza inicial: Vacío
    if (val.length === 0) {
        if (isSubmit) {
            // Si es submit, el vacío es ERROR
            setInputInvalid(input);
            // Shake
            input.classList.add('animate-shake');
            setTimeout(() => input.classList.remove('animate-shake'), 300);

            checkIcon.classList.add('hidden');
            errorIcon.classList.remove('hidden');

            errorMsg.innerText = 'El nombre es obligatorio';
            errorMsg.classList.remove('hidden');
            return 'empty'; // Retornamos código de error
        } else {
            // Si es typing normal, el vacío es neutral
            errorMsg.classList.add('hidden');
            resetInputStyle(input);

            checkIcon.classList.add('hidden');
            errorIcon.classList.add('hidden');
            lottieSuccess.classList.add('hidden');
            return false;
        }
    }

    // 3. Validar Longitud Minima (5 chars)
    if (val.length < 5) {
        setInputInvalid(input);
        checkIcon.classList.add('hidden');
        errorIcon.classList.remove('hidden');
        lottieSuccess.classList.add('hidden');

        errorMsg.innerText = 'Mínimo 5 caracteres';
        errorMsg.classList.remove('hidden');
        return 'short'; // Código de error
    }

    // Valid Success
    setInputValid(input);
    checkIcon.classList.add('hidden'); // Hide static check
    errorIcon.classList.add('hidden');

    // Mostramos y reiniciamos Lottie
    lottieSuccess.classList.remove('hidden');
    const player = lottieSuccess.querySelector('lottie-player');
    if (player) {
        player.seek(0);
        player.play();
    }

    errorMsg.classList.add('hidden');
    return true;
}

function validateDni(input, isSubmit = false) {
    // Solo números y max length
    let val = input.value.replace(/\D/g, '');
    if (val.length > 11) val = val.substring(0, 11);

    input.value = val;

    const checkIcon = document.getElementById('dni-check');
    const errorIcon = document.getElementById('dni-error');
    const errorText = document.getElementById('dniErrorText');
    const lottieSuccess = document.getElementById('dni-lottie-success');

    if (val.length === 0) {
        if (isSubmit) {
            // Empty On Submit -> Error
            setInputInvalid(input);
            input.classList.add('animate-shake');
            setTimeout(() => input.classList.remove('animate-shake'), 300);

            checkIcon.classList.add('hidden');
            errorIcon.classList.remove('hidden');
            errorText.innerText = 'Documento obligatorio';
            errorText.classList.remove('hidden');
            return 'empty';
        } else {
            // Typing Empty -> Neutral
            resetInputStyle(input);
            checkIcon.classList.add('hidden');
            errorIcon.classList.add('hidden');
            errorText.classList.add('hidden');
            return false;
        }
    }

    if (val.length === 8 || val.length === 11) {
        // Valido
        setInputValid(input);
        checkIcon.classList.add('hidden');
        errorIcon.classList.add('hidden');
        errorText.classList.add('hidden');

        // Mostramos y reiniciamos Lottie
        lottieSuccess.classList.remove('hidden');
        const player = lottieSuccess.querySelector('lottie-player');
        if (player) {
            player.seek(0);
            player.play();
        }
        return true;
    } else {
        // Invalido
        setInputInvalid(input);
        checkIcon.classList.add('hidden');
        errorIcon.classList.remove('hidden');
        lottieSuccess.classList.add('hidden');
        errorText.innerText = 'Debe ser 8 (DNI) u 11 (RUC) dígitos';
        errorText.classList.remove('hidden');
        return 'format';
    }
}

function setInputValid(input) {
    input.classList.remove('ring-slate-100', 'focus:ring-indigo-600', 'ring-red-300', 'focus:ring-red-500', 'bg-red-50');
    input.classList.add('ring-green-400', 'focus:ring-green-500', 'bg-green-50');
}

function setInputInvalid(input) {
    input.classList.remove('ring-slate-100', 'focus:ring-indigo-600', 'ring-green-400', 'focus:ring-green-500', 'bg-green-50');
    input.classList.add('ring-red-300', 'focus:ring-red-500', 'bg-red-50');
}

function resetInputStyle(input) {
    input.classList.remove('ring-green-400', 'focus:ring-green-500', 'bg-green-50', 'ring-red-300', 'focus:ring-red-500', 'bg-red-50');
    input.classList.add('ring-slate-100', 'focus:ring-indigo-600');
    input.style.backgroundColor = '';
}

// Live Validation Listeners
const nameInput = document.getElementById('clientName');
if (nameInput) {
    nameInput.addEventListener('input', function () {
        validateName(this, false);
    });
}

const docInput = document.getElementById('docNumber');
if (docInput) {
    docInput.addEventListener('input', function () {
        validateDni(this, false);
    });
}

// --- Send Logic ---
function sendProformaRequest() {
    const docInput = document.getElementById('docNumber');
    const nameInput = document.getElementById('clientName');

    const doc = docInput.value.trim();
    const name = nameInput.value.trim();

    // VARIABLES GLOBALES INYECTADAS PHP
    // Se asume que en la vista se definen MANAGER_PHONE, PRODUCT_NAME, MANAGER_NAME
    // Si no, tendremos que pasarlas como argumentos o leerlas del DOM.
    // Para no romper, leeremos de variables globales que definiremos en la vista antes de cargar este script.

    // --- VALIDATE BOTH FIELDS FOR ALERTS ---
    const nameStatus = validateName(nameInput, true);
    const docStatus = validateDni(docInput, true);

    let hasError = false;

    // Name Errors
    if (nameStatus !== true) {
        hasError = true;
        if (nameStatus === 'empty') mostrarNotificacion('Nombre Vacío', 'Por favor ingresa tu nombre/empresa', 'error');
        else if (nameStatus === 'short') mostrarNotificacion('Nombre muy corto', 'Mínimo 5 caracteres', 'error');
        else mostrarNotificacion('Error Nombre', 'Verifica el nombre', 'error');
    }

    // DNI Errors (Delayed slightly so they stack nicely if both fail)
    if (docStatus !== true) {
        hasError = true;
        setTimeout(() => {
            if (docStatus === 'empty') mostrarNotificacion('Documento Vacío', 'El DNI o RUC es obligatorio', 'error');
            else if (docStatus === 'format') mostrarNotificacion('Documento Inválido', 'Debe tener 8 u 11 dígitos exactos', 'error');
            else mostrarNotificacion('Error Documento', 'Verifica el documento', 'error');
        }, 150); // Small 150ms delay makes the second toast pop distinctly
    }

    if (hasError) {
        // Focus logic: If name is bad, focus name. If name is good but doc broken, focus doc.
        if (nameStatus !== true) nameInput.focus();
        else docInput.focus();
        return;
    }

    // CAPTURA DE LEAD (Async)
    // Usamos variables globales definidas en la vista
    if (typeof PRODUCT_ORIGIN !== 'undefined') {
        const safeOrigen = PRODUCT_ORIGIN;

        fetch('/api/leads/store', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre: name, dni_ruc: doc, origen: safeOrigen })
        }).then(r => r.json()).then(d => console.log('Lead saved', d)).catch(e => console.error(e));
    }

    // REQUEST MESSAGE LOGIC
    const managerName = (typeof MANAGER_NAME !== 'undefined') ? MANAGER_NAME : 'Asesor';
    const managerPhone = (typeof MANAGER_PHONE !== 'undefined') ? MANAGER_PHONE : '';
    const producto = (typeof PRODUCT_NAME !== 'undefined') ? PRODUCT_NAME : 'Producto';

    let intro;
    if (doc.length === 11) {
        // Es RUC -> Mensaje corporativo
        intro = `Somos de la empresa *${name}* y nuestro RUC es ${doc}`;
    } else {
        // Es DNI -> Mensaje personal
        intro = `Mi nombre es *${name}* y mi DNI es ${doc}`;
    }

    let text = `Hola, ${managerName}.\n\n`;
    text += `${intro}.\n\n`;
    text += `Le escribo para solicitar una proforma de esta *${producto}* que he visto en su página web.\n`;
    text += `${window.location.href}\n\n`;
    text += `Quedo atento(a). Gracias.`;

    // Send to Manager's number
    const url = `https://wa.me/${managerPhone}?text=${encodeURIComponent(text)}`;

    mostrarNotificacion('Redirigiendo', 'Abriendo WhatsApp...', 'success');

    // CELEBRATION! Confetti
    confetti({
        particleCount: 150,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#25D366', '#128C7E', '#34B7F1', '#ffffff']
    });

    playFeedbackSound('success');

    setTimeout(() => {
        window.open(url, '_blank');
        closeModal();
    }, 1000);
}

// Header Widget Logic
function toggleContact() {
    const panel = document.getElementById('contactPanel');
    const btn = document.getElementById('contactBtn');

    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        setTimeout(() => {
            document.addEventListener('click', closeContactOnOutsideClick);
        }, 100);
    } else {
        panel.classList.add('hidden');
        document.removeEventListener('click', closeContactOnOutsideClick);
    }
}

function closeContactOnOutsideClick(e) {
    const panel = document.getElementById('contactPanel');
    const btn = document.getElementById('contactBtn');
    if (!panel.contains(e.target) && !btn.contains(e.target)) {
        panel.classList.add('hidden');
        document.removeEventListener('click', closeContactOnOutsideClick);
    }
}

function copyNumber(btn) {
    // MANAGER_PHONE debe estar definido globalmente
    const number = (typeof MANAGER_PHONE !== 'undefined') ? MANAGER_PHONE : '';
    navigator.clipboard.writeText(number).then(() => {
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="ph-bold ph-check text-green-500"></i>';
        btn.classList.add('bg-green-50');

        mostrarNotificacion('Copiado', 'Número copiado al portapapeles', 'success');

        setTimeout(() => {
            btn.innerHTML = originalIcon;
            btn.classList.remove('bg-green-50');
        }, 2000);
    });
}

// Input Enter Key
document.getElementById('clientName')?.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        sendProformaRequest();
    }
});
document.getElementById('docNumber').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        // searchDni();
    }
});

// --- UX Sounds (Native Audio API) ---
function playFeedbackSound(type) {
    const sounds = {
        open: 'https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3', // Soft pop
        success: 'https://assets.mixkit.co/active_storage/sfx/1435/1435-preview.mp3', // Success chime
        error: 'https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3' // Error bump
    };

    const audio = new Audio(sounds[type]);
    audio.volume = 0.3; // Low profile
    audio.play().catch(e => console.log('Audio disabled by browser'));
}

// Initialize AOS
window.addEventListener('load', () => {
    AOS.init({
        duration: 800,
        easing: 'ease-in-out-cubic',
        once: true,
        mirror: false
    });
});
