/**
 * Cualquier <form data-debounce-search> envía automáticamente el
 * formulario ~400ms después de que el usuario deja de escribir en su
 * primer input de texto, evitando peticiones innecesarias por cada tecla.
 */
export function initSearchDebounce(): void {
    const forms = document.querySelectorAll<HTMLFormElement>('form[data-debounce-search]');

    forms.forEach((form) => {
        const input = form.querySelector<HTMLInputElement>('input[type="text"]');

        if (!input) {
            return;
        }

        let timeoutId: ReturnType<typeof setTimeout> | undefined;

        input.addEventListener('input', () => {
            if (timeoutId) {
                clearTimeout(timeoutId);
            }

            timeoutId = setTimeout(() => form.requestSubmit(), 400);
        });
    });
}
