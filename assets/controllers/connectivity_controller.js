import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['badge', 'label'];
    static values = {
        interval: { type: Number, default: 10000 },
        endpoint: { type: String, default: '/healthz' }
    };

    connect() {
        this._boundCheck = this._check.bind(this);
        this._timer = null;
        this._abort = null;
        this._stateUnknown = true;

        window.addEventListener('online', this._boundCheck);
        window.addEventListener('offline', this._boundCheck);

        this._check();
        this._startPolling();
    }

    disconnect() {
        window.removeEventListener('online', this._boundCheck);
        window.removeEventListener('offline', this._boundCheck);
        this._stopPolling();
        if (this._abort) {
            try { this._abort.abort(); } catch (e) { /* noop */ }
        }
    }

    _startPolling() {
        this._stopPolling();
        const ms = Math.max(2000, Number(this.intervalValue) || 10000);
        this._timer = setInterval(this._boundCheck, ms);
    }

    _stopPolling() {
        if (this._timer) { clearInterval(this._timer); this._timer = null; }
    }

    async _check() {
        if (!navigator.onLine) {
            this._apply(false, 'Hors ligne');
            return;
        }
        try {
            if (this._abort) {
                try { this._abort.abort(); } catch (e) { /* noop */ }
            }
            this._abort = new AbortController();
            const res = await fetch(this.endpointValue, {
                method: 'HEAD',
                cache: 'no-store',
                signal: this._abort.signal,
                headers: { 'X-Health-Check': '1' }
            });
            const ok = res.ok;
            const label = ok ? 'En ligne' : 'Serveur défaillant';
            this._apply(ok, label);
        } catch (err) {
            const isNetworkErr =
                err.name === 'AbortError' ? false :
                (err instanceof TypeError) ||
                /network|fetch|connection/i.test(err.message || '');
            if (err.name === 'AbortError') return;
            const label = isNetworkErr ? 'Hors ligne' : 'Serveur défaillant';
            this._apply(false, label);
        }
    }

    _apply(online, label) {
        const statusClass = online ? 'is-online' : 'is-offline';

        if (this.hasBadgeTarget) {
            this.badgeTarget.classList.remove('is-online', 'is-offline');
            this.badgeTarget.classList.add(statusClass);
            if (this.badgeTarget.tagName === 'DIV' || this.badgeTarget.tagName === 'SPAN') {
                if (!online) this.badgeTarget.classList.add('offline');
                else this.badgeTarget.classList.remove('offline');
            }
        }

        if (this.hasLabelTarget) {
            this.labelTarget.textContent = label;
        }
    }
}
