import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'hint'];

    connect() {
        this.deferredPrompt = null;
        this.handleBeforeInstallPrompt = this.onBeforeInstallPrompt.bind(this);
        this.handleAppInstalled = this.onAppInstalled.bind(this);

        window.addEventListener('beforeinstallprompt', this.handleBeforeInstallPrompt);
        window.addEventListener('appinstalled', this.handleAppInstalled);

        this.hideButton();
    }

    disconnect() {
        window.removeEventListener('beforeinstallprompt', this.handleBeforeInstallPrompt);
        window.removeEventListener('appinstalled', this.handleAppInstalled);
    }

    async prompt() {
        if (!this.deferredPrompt) {
            this.showHint('Installation non disponible pour le moment sur cet appareil.');
            return;
        }

        this.deferredPrompt.prompt();
        await this.deferredPrompt.userChoice;
        this.deferredPrompt = null;
        this.hideButton();
    }

    onBeforeInstallPrompt(event) {
        event.preventDefault();
        this.deferredPrompt = event;
        this.showButton();
        this.showHint("L'application peut maintenant etre installee sur l'ecran d'accueil.");
    }

    onAppInstalled() {
        this.deferredPrompt = null;
        this.hideButton();
        this.showHint('Application installee avec succes.');
    }

    showButton() {
        if (this.hasButtonTarget) {
            this.buttonTarget.hidden = false;
        }
    }

    hideButton() {
        if (this.hasButtonTarget) {
            this.buttonTarget.hidden = true;
        }
    }

    showHint(message) {
        if (this.hasHintTarget) {
            this.hintTarget.textContent = message;
        }
    }
}
