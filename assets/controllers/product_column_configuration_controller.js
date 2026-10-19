import { Controller } from '@hotwired/stimulus';
import { Sortable } from 'sortablejs';
import {
    generateCsrfHeaders,
    generateCsrfToken,
} from './csrf_protection_controller.js';

export default class extends Controller {
    async load(event) {
        event.preventDefault();

        const url = event.currentTarget.getAttribute('href');
        const targetUrl = event.currentTarget.dataset.targetUrl;
        const saveUrl = event.currentTarget.dataset.saveUrl;

        if (!url || !targetUrl || !saveUrl) {
            return;
        }

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const html = await response.text();

        this.showModal(html, targetUrl, saveUrl);
    }

    showModal(html, targetUrl, saveUrl) {
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

        const saveButton = modalElement.querySelector(
            '[data-product-column-configuration-save]',
        );

        saveButton?.addEventListener(
            'click',
            () => this.save(modalElement, saveUrl, targetUrl),
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

    async save(modalElement, saveUrl, targetUrl) {
        const configuredList = modalElement.querySelector(
            '[data-product-column-configuration-list="configured"]',
        );

        if (!configuredList) {
            return;
        }

        const fieldTypeIds = [...configuredList.children]
            .map((item) => item.dataset.fieldTypeId);

        const csrfField = modalElement.querySelector(
            'input[data-controller="csrf-protection"]',
        );

        if (!csrfField) {
            throw new Error('CSRF token not found');
        }

        const csrfForm = document.createElement('form');
        csrfForm.append(csrfField);

        generateCsrfToken(csrfForm);

        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...generateCsrfHeaders(csrfForm),
            },
            body: JSON.stringify({ fieldTypeIds }),
        });

        window.location.href = targetUrl;
    }
}
