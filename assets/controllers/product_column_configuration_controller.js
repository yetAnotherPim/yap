import { Controller } from '@hotwired/stimulus';
import { Sortable } from 'sortablejs';

export default class extends Controller {
    async load(event) {
        event.preventDefault();

        const url = event.currentTarget.getAttribute('href');

        if (!url) {
            return;
        }

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const html = await response.text();

        this.showModal(html);
    }

    showModal(html) {
        const modalElement = document.createElement('div');

        modalElement.classList.add('modal', 'fade');
        modalElement.tabIndex = -1;

        modalElement.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    ${html}
                </div>
            </div>
        `;

        document.body.appendChild(modalElement);

        const modal = new bootstrap.Modal(modalElement);

        modalElement.addEventListener(
            'shown.bs.modal',
            () => this.initializeSortable(modalElement),
            { once: true },
        );

        modalElement.addEventListener(
            'hidden.bs.modal',
            () => modalElement.remove(),
            { once: true },
        );

        modal.show();
    }

    initializeSortable(modalElement) {
        const configuredList = modalElement.querySelector(
            '[data-product-column-configuration-list="configured"]',
        );

        const unconfiguredList = modalElement.querySelector(
            '[data-product-column-configuration-list="unconfigured"]',
        );

        if (!configuredList || !unconfiguredList) {
            return;
        }

        const options = {
            animation: 150,
            group: 'product-columns',
        };

        Sortable.create(configuredList, options);
        Sortable.create(unconfiguredList, options);
    }
}
