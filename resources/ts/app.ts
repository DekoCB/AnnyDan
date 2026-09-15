import './bootstrap';

import Alpine from 'alpinejs';
import { initConfirmActions } from './modules/confirm-actions';
import { initSearchDebounce } from './modules/search-debounce';
import { initDocumentoValidation } from './modules/documento-validation';
import { initVentaForm } from './modules/venta-form';
import { initMovimientoCajaForm } from './modules/movimiento-caja-form';

window.Alpine = Alpine;

Alpine.start();

initConfirmActions();
initSearchDebounce();
initDocumentoValidation('#cliente-form');
initDocumentoValidation('#proveedor-form');
initVentaForm();
initMovimientoCajaForm();
