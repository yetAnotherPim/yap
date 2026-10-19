import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'selectAll',
        'allSelected',
    ];

    static values = {
        totalCount: Number,
    };

    connect() {
        this.allSelected = false;

        this.handleCheckboxChange = this.handleCheckboxChange.bind(this);

        document.addEventListener('change', this.handleCheckboxChange);

        this.updateActionCounts();
    }

    disconnect() {
        document.removeEventListener('change', this.handleCheckboxChange);
    }

    handleCheckboxChange(event) {
        if (!event.target.matches(
            '.form-batch-checkbox, .form-batch-checkbox-all',
        )) {
            return;
        }

        requestAnimationFrame(() => {
            if (this.allSelected) {
                this.allSelected = false;
                this.setBatchActionAll(false);
            }

            this.updateActionCounts();
            this.updateSelectionOption();
        });
    }

    updateActionCounts() {
        const selectedCount = this.getSelectedCount();

        this.updateActionCount(
            'edit',
            this.allSelected ? this.totalCountValue : selectedCount,
        );

        this.updateActionCount(
            'delete',
            selectedCount,
        );
    }

    updateActionCount(actionName, count) {
        const action = document.querySelector(
            `[data-product-batch-count-action="${actionName}"]`,
        );

        if (!action) {
            return;
        }

        let countElement = action.querySelector(
            '.product-batch-action-count',
        );

        if (!countElement) {
            countElement = document.createElement('span');
            countElement.classList.add('product-batch-action-count');

            action.appendChild(document.createTextNode(' '));
            action.appendChild(countElement);
        }

        countElement.textContent = `(${count.toLocaleString()})`;
    }

    updateSelectionOption() {
        const selectedCount = this.getSelectedCount();

        if (selectedCount === 0) {
            this.selectAllTarget.classList.add('d-none');
            this.allSelectedTarget.classList.add('d-none');

            return;
        }

        if (this.allSelected) {
            this.selectAllTarget.classList.add('d-none');
            this.allSelectedTarget.classList.remove('d-none');

            return;
        }

        if (selectedCount === this.getPageCount()) {
            this.selectAllTarget.classList.remove('d-none');
        } else {
            this.selectAllTarget.classList.add('d-none');
        }

        this.allSelectedTarget.classList.add('d-none');
    }

    selectAll() {
        this.allSelected = true;
        this.setBatchActionAll(true);

        this.selectAllTarget.classList.add('d-none');
        this.allSelectedTarget.classList.remove('d-none');

        this.updateActionCounts();
    }

    clearSelection() {
        this.allSelected = false;
        this.setBatchActionAll(false);

        this.allSelectedTarget.classList.add('d-none');

        const selectAllCheckbox = document.querySelector(
            '.form-batch-checkbox-all',
        );

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }

        document.querySelectorAll(
            '.form-batch-checkbox:checked',
        ).forEach((checkbox) => {
            checkbox.checked = false;
        });

        this.updateActionCounts();
        this.updateSelectionOption();
    }

    setBatchActionAll(value) {
        const action = this.getEditAction();

        if (!action) {
            return;
        }

        const url = new URL(action.href);

        if (!value) {
            url.searchParams.delete('batchActionAll');
        }

        if (value) {
            url.searchParams.set('batchActionAll', '1');
        }

        const actionUrl = url.toString();

        action.href = actionUrl;
        action.dataset.actionUrl = actionUrl;
    }

    getEditAction() {
        return document.querySelector(
            '[data-product-batch-count-action="edit"]',
        );
    }

    getSelectedCount() {
        return document.querySelectorAll(
            '.form-batch-checkbox:checked',
        ).length;
    }

    getPageCount() {
        return document.querySelectorAll(
            '.form-batch-checkbox',
        ).length;
    }
}
