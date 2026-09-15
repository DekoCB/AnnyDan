import type { Alpine as AlpineType } from 'alpinejs';
import type { AxiosStatic } from 'axios';

declare global {
    interface Window {
        Alpine: AlpineType;
        axios: AxiosStatic;
    }
}

export {};
