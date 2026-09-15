/**
 * Formulario dinámico de ítems para registrar una venta. Todo el cálculo
 * aquí es SOLO visual/referencial (mejora la UX) — Laravel vuelve a
 * calcular y valida subtotal/IGV/total en CalculoVentaService antes de
 * guardar, que es la única fuente de verdad. Ver sección 2.7.
 */
function formatearMoneda(valor: number): string {
    return `S/ ${valor.toFixed(2)}`;
}

export function initVentaForm(): void {
    const form = document.querySelector<HTMLFormElement>('#venta-form');

    if (!form) {
        return;
    }

    const tasaIgv = parseFloat(form.dataset.tasaIgv ?? '0');
    const tbody = document.querySelector<HTMLTableSectionElement>('#items-body');
    const template = document.querySelector<HTMLTemplateElement>('#item-row-template');
    const agregarBtn = document.querySelector<HTMLButtonElement>('#agregar-item');
    const errorEl = document.querySelector<HTMLParagraphElement>('#items-client-error');
    const subtotalEl = document.querySelector<HTMLElement>('#resumen-subtotal');
    const igvEl = document.querySelector<HTMLElement>('#resumen-igv');
    const totalEl = document.querySelector<HTMLElement>('#resumen-total');

    if (!tbody || !template || !agregarBtn || !subtotalEl || !igvEl || !totalEl) {
        return;
    }

    let indice = 0;

    function recalcularResumen(): void {
        let subtotal = 0;

        tbody!.querySelectorAll<HTMLTableRowElement>('.item-row').forEach((fila) => {
            const cantidad = parseFloat(fila.querySelector<HTMLInputElement>('.item-cantidad')?.value ?? '0') || 0;
            const precio = parseFloat(fila.querySelector<HTMLInputElement>('.item-precio')?.value ?? '0') || 0;
            subtotal += cantidad * precio;
        });

        const igv = subtotal * tasaIgv;
        const total = subtotal + igv;

        subtotalEl!.textContent = formatearMoneda(subtotal);
        igvEl!.textContent = formatearMoneda(igv);
        totalEl!.textContent = formatearMoneda(total);
    }

    function actualizarFila(fila: HTMLTableRowElement): void {
        const cantidad = parseFloat(fila.querySelector<HTMLInputElement>('.item-cantidad')?.value ?? '0') || 0;
        const precio = parseFloat(fila.querySelector<HTMLInputElement>('.item-precio')?.value ?? '0') || 0;

        const subtotalCell = fila.querySelector<HTMLElement>('.item-subtotal');
        if (subtotalCell) {
            subtotalCell.textContent = formatearMoneda(cantidad * precio);
        }

        recalcularResumen();
    }

    function agregarFila(): void {
        const fragment = template!.content.cloneNode(true) as DocumentFragment;
        const fila = fragment.querySelector<HTMLTableRowElement>('.item-row');

        if (!fila) {
            return;
        }

        fila.innerHTML = fila.innerHTML.replaceAll('__INDEX__', String(indice));
        indice += 1;

        tbody!.appendChild(fila);

        fila.querySelector<HTMLInputElement>('.item-cantidad')
            ?.addEventListener('input', () => actualizarFila(fila));
        fila.querySelector<HTMLInputElement>('.item-precio')
            ?.addEventListener('input', () => actualizarFila(fila));
        fila.querySelector<HTMLButtonElement>('.quitar-item')
            ?.addEventListener('click', () => {
                fila.remove();
                recalcularResumen();
            });
    }

    agregarBtn.addEventListener('click', agregarFila);

    form.addEventListener('submit', (event) => {
        const filas = tbody!.querySelectorAll<HTMLTableRowElement>('.item-row');

        if (filas.length === 0) {
            event.preventDefault();
            errorEl?.classList.remove('hidden');

            if (errorEl) {
                errorEl.textContent = 'Agrega al menos un ítem antes de registrar la venta.';
            }
        } else {
            errorEl?.classList.add('hidden');
        }
    });

    agregarFila();
}
