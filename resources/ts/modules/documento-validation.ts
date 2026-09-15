/**
 * Validación interactiva del número de documento según su tipo, reutilizada
 * por los formularios de clientes y proveedores. Mejora la UX detectando
 * errores antes de enviar, pero replica reglas que Laravel vuelve a validar
 * en el backend (Store/UpdateClienteRequest, Store/UpdateProveedorRequest)
 * — el backend sigue siendo la fuente de verdad, ver sección 2.7/2.8.
 */
const DOCUMENTO_PATTERNS: Record<string, { pattern: RegExp; mensaje: string }> = {
    DNI: { pattern: /^\d{8}$/, mensaje: 'El DNI debe tener exactamente 8 dígitos.' },
    RUC: { pattern: /^\d{11}$/, mensaje: 'El RUC debe tener exactamente 11 dígitos.' },
};

function mostrarError(input: HTMLInputElement, mensaje: string | null): void {
    const existente = input.parentElement?.querySelector<HTMLParagraphElement>('[data-client-error]');
    existente?.remove();

    input.classList.toggle('border-red-500', Boolean(mensaje));

    if (!mensaje) {
        return;
    }

    const error = document.createElement('p');
    error.dataset.clientError = 'true';
    error.className = 'text-sm text-red-600 dark:text-red-400 mt-2';
    error.textContent = mensaje;
    input.insertAdjacentElement('afterend', error);
}

function validarDocumento(tipoSelect: HTMLSelectElement, numeroInput: HTMLInputElement): boolean {
    const regla = DOCUMENTO_PATTERNS[tipoSelect.value];

    if (!regla) {
        mostrarError(numeroInput, null);
        return true;
    }

    const valido = regla.pattern.test(numeroInput.value.trim());
    mostrarError(numeroInput, valido ? null : regla.mensaje);

    return valido;
}

export function initDocumentoValidation(formSelector: string): void {
    const form = document.querySelector<HTMLFormElement>(formSelector);

    if (!form) {
        return;
    }

    const tipoSelect = form.querySelector<HTMLSelectElement>('#tipo_documento');
    const numeroInput = form.querySelector<HTMLInputElement>('#numero_documento');

    if (!tipoSelect || !numeroInput) {
        return;
    }

    numeroInput.addEventListener('blur', () => validarDocumento(tipoSelect, numeroInput));
    tipoSelect.addEventListener('change', () => validarDocumento(tipoSelect, numeroInput));

    form.addEventListener('submit', (event) => {
        if (!validarDocumento(tipoSelect, numeroInput)) {
            event.preventDefault();
            numeroInput.focus();
        }
    });
}
