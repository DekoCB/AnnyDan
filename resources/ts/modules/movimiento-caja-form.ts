/**
 * Filtra las opciones de categoría según el tipo de movimiento
 * seleccionado (ingreso/egreso), para que el usuario no pueda ni
 * intentar una combinación inconsistente. El backend vuelve a validar
 * esta correspondencia de todas formas (StoreMovimientoCajaRequest).
 */
export function initMovimientoCajaForm(): void {
    const form = document.querySelector<HTMLFormElement>('#movimiento-caja-form');

    if (!form) {
        return;
    }

    const tipoSelect = form.querySelector<HTMLSelectElement>('#tipo');
    const categoriaSelect = form.querySelector<HTMLSelectElement>('#categoria_id');

    if (!tipoSelect || !categoriaSelect) {
        return;
    }

    function aplicarFiltro(): void {
        const tipo = tipoSelect!.value;
        const opcionesVisibles: HTMLOptionElement[] = [];

        for (const opcion of Array.from(categoriaSelect!.options)) {
            const coincide = opcion.dataset.tipo === tipo;
            opcion.hidden = !coincide;

            if (coincide) {
                opcionesVisibles.push(opcion);
            }
        }

        const haySeleccionValida = opcionesVisibles.some((opcion) => opcion.selected);

        if (!haySeleccionValida && opcionesVisibles.length > 0) {
            opcionesVisibles[0].selected = true;
        }
    }

    tipoSelect.addEventListener('change', aplicarFiltro);
    aplicarFiltro();
}
