import '@hotwired/turbo';
import './stimulus_bootstrap.js';
import './styles/app.css';

document.documentElement.classList.add('js');

if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            await navigator.serviceWorker.register('/sw.js', { scope: '/' });
            console.info('Terrain PWA service worker registered.');
        } catch (error) {
            console.error('Unable to register Terrain service worker.', error);
        }
    });
}
