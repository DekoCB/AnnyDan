/**
 * Alterna el tema claro/oscuro y lo persiste en localStorage. La
 * aplicación INICIAL del tema (para evitar parpadeo) ocurre en un script
 * inline en <head> — ver resources/views/layouts/app.blade.php y
 * layouts/guest.blade.php — porque debe correr antes de que Vite cargue
 * este módulo. Este archivo conecta el botón de alternancia y, cuando el
 * navegador lo soporta, anima el cambio con un barrido circular (View
 * Transitions API) que nace desde el propio botón.
 */
const STORAGE_KEY = 'annydan-theme';
const DURACION_MS = 550;

function guardarPreferencia(esOscuro: boolean): void {
    try {
        localStorage.setItem(STORAGE_KEY, esOscuro ? 'dark' : 'light');
    } catch {
        // localStorage puede no estar disponible (modo privado); el
        // tema simplemente no persiste entre visitas.
    }
}

function aplicarTema(esOscuro: boolean): void {
    document.documentElement.classList.toggle('dark', esOscuro);
    guardarPreferencia(esOscuro);
}

function animarBarridoCircular(origenX: number, origenY: number, esOscuroSiguiente: boolean): void {
    const radioFinal = Math.hypot(
        Math.max(origenX, window.innerWidth - origenX),
        Math.max(origenY, window.innerHeight - origenY),
    );

    const transicion = document.startViewTransition?.(() => {
        aplicarTema(esOscuroSiguiente);
    });

    if (!transicion) {
        aplicarTema(esOscuroSiguiente);

        return;
    }

    transicion.ready
        .then(() => {
            document.documentElement.animate(
                {
                    clipPath: [
                        `circle(0px at ${origenX}px ${origenY}px)`,
                        `circle(${radioFinal}px at ${origenX}px ${origenY}px)`,
                    ],
                },
                {
                    duration: DURACION_MS,
                    easing: 'cubic-bezier(0.65, 0, 0.35, 1)',
                    pseudoElement: '::view-transition-new(root)',
                },
            );
        })
        .catch(() => {
            // Si la animación no puede iniciarse (navegador sin soporte
            // completo), el tema ya quedó aplicado por el callback.
        });
}

export function initThemeToggle(): void {
    const boton = document.querySelector<HTMLButtonElement>('#theme-toggle');

    if (!boton) {
        return;
    }

    boton.addEventListener('click', (evento) => {
        const esOscuroSiguiente = !document.documentElement.classList.contains('dark');
        const prefiereMenosMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!document.startViewTransition || prefiereMenosMovimiento) {
            aplicarTema(esOscuroSiguiente);

            return;
        }

        const { clientX, clientY } = evento;
        animarBarridoCircular(clientX, clientY, esOscuroSiguiente);
    });
}
