import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['badge', 'label'];

    connect() {
        this.boundRefresh = this.refresh.bind(this);

        window.addEventListener('online', this.boundRefresh);
        window.addEventListener('offline', this.boundRefresh);

        this.refresh();
    }

    disconnect() {
        window.removeEventListener('online', this.boundRefresh);
        window.removeEventListener('offline', this.boundRefresh);
    }

    refresh() {
        const online = navigator.onLine;
        const statusClass = online ? 'is-online' : 'is-offline';
        const statusLabel = online ? 'En ligne' : 'Hors ligne';

        if (this.hasBadgeTarget) {
            this.badgeTarget.classList.remove('is-online', 'is-offline');
            this.badgeTarget.classList.add(statusClass);
        }

        if (this.hasLabelTarget) {
            this.labelTarget.textContent = statusLabel;
        }
    }
}
