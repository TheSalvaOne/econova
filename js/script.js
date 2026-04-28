/* ============================================
INIT AOS
============================================ */
AOS.init({
    once: true,           // Anima solo la primera vez
    offset: 80,           // Cuántos px antes de entrar en vista
    easing: 'ease-out-quart',
});

/* ============================================
CURSOR PERSONALIZADO
============================================ */
const cursor = document.getElementById('cursorPersonalizado');
let cursorX = 0, cursorY = 0;
let rafId;

document.addEventListener('mousemove', (e) => {
    cursorX = e.clientX;
    cursorY = e.clientY;
    if (!rafId) {
        rafId = requestAnimationFrame(() => {
            if (cursor) { // Verificación de seguridad
                cursor.style.left = cursorX + 'px';
                cursor.style.top  = cursorY + 'px';
            }
            rafId = null;
        });
    }
});

// Efecto hover en elementos interactivos
const elementosInteractivos = document.querySelectorAll('button, a, input, select, .tarjetaProtocolo');
elementosInteractivos.forEach(el => {
    el.addEventListener('mouseenter', () => cursor?.classList.add('cursorActivo'));
    el.addEventListener('mouseleave', () => cursor?.classList.remove('cursorActivo'));
});

/* ============================================
VALIDACIÓN DEL FORMULARIO
============================================ */
const formulario    = document.getElementById('formularioContacto');
const mensajeExito  = document.getElementById('mensajeExito');

function validarCampo(grupo, campo) {
    if (!grupo || !campo) return false;
    let esValido = false;

    if (campo.type === 'email') {
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        esValido = regexEmail.test(campo.value.trim());
    } else {
        esValido = campo.value.trim() !== '';
    }

    grupo.classList.toggle('esValido',   esValido);
    grupo.classList.toggle('esInvalido', !esValido);

    return esValido;
}

if (formulario) {
    // Validación en tiempo real
    formulario.querySelectorAll('input, select').forEach(campo => {
        const grupo = campo.closest('.grupoInput');
        campo.addEventListener('blur', () => validarCampo(grupo, campo));
        campo.addEventListener('input', () => {
            if (grupo.classList.contains('esInvalido')) {
                validarCampo(grupo, campo);
            }
        });
    });

    // Validación al enviar
    formulario.addEventListener('submit', (e) => {
        e.preventDefault();

        const camposAValidar = [
            { grupoId: 'grupoNombre',   campoId: 'campoNombre' },
            { grupoId: 'grupoEmail',    campoId: 'campoEmail' },
            { grupoId: 'grupoServicio', campoId: 'campoServicio' },
        ];

        let formularioValido = true;

        camposAValidar.forEach(({ grupoId, campoId }) => {
            const grupo = document.getElementById(grupoId);
            const campo = document.getElementById(campoId);
            if (!validarCampo(grupo, campo)) {
                formularioValido = false;
            }
        });

        if (formularioValido) {
            formulario.style.display = 'none';
            mensajeExito?.classList.add('visible');
        } else {
            const primerInvalido = formulario.querySelector('.esInvalido input, .esInvalido select');
            if (primerInvalido) primerInvalido.focus();
        }
    });
}

/* ============================================
CONTADORES ANIMADOS
============================================ */
function animarContador(elemento) {
    const objetivo  = parseInt(elemento.dataset.objetivo, 10);
    const sufijo    = elemento.dataset.sufijo || '';
    const duracion  = 1800; 
    const inicio    = performance.now();

    function paso(ahora) {
        const progreso  = Math.min((ahora - inicio) / duracion, 1);
        const easedProg = 1 - Math.pow(1 - progreso, 3);
        const valorActual = Math.floor(easedProg * objetivo);
        elemento.textContent = valorActual + sufijo;

        if (progreso < 1) {
            requestAnimationFrame(paso);
        }
    }
    requestAnimationFrame(paso);
}

const observadorContadores = new IntersectionObserver((entradas) => {
    entradas.forEach(entrada => {
        if (entrada.isIntersecting) {
            animarContador(entrada.target);
            observadorContadores.unobserve(entrada.target);
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.numeroContador').forEach(num => {
    observadorContadores.observe(num);
});