import { startStimulusApp } from '@symfony/stimulus-bridge';
import { registerReactControllerComponents } from '@symfony/ux-react';
import Notification from '@stimulus-components/notification';

registerReactControllerComponents(
  require.context('./react/controllers', true, /\.(j|t)sx?$/)
);

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(
  require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
  )
);

app.register('notification', Notification);
