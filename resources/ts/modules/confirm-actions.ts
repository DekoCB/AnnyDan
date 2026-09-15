/**
 * Cualquier <form data-confirm="mensaje"> pide confirmación antes de
 * enviarse. Usado para operaciones destructivas/críticas (desactivar
 * clientes, anular ventas, etc.) — ver sección 18 del requerimiento.
 */
export function initConfirmActions(): void {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const message = form.dataset.confirm;

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
}
