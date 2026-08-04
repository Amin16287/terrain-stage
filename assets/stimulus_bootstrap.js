import { startStimulusApp } from '@symfony/stimulus-bundle';
import ConnectivityController from './controllers/connectivity_controller.js';
import InstallPromptController from './controllers/install_prompt_controller.js';

const app = startStimulusApp();
app.register('connectivity', ConnectivityController);
app.register('install-prompt', InstallPromptController);
