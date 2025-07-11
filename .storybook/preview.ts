import type { Preview } from '@storybook/react';
import { tutorConfig } from '../assets/react/v3/shared/config/config';

tutorConfig.tutor_url = tutorConfig.tutor_url || `${process.env.CYPRESS_base_url}/wp-content/plugins/tutor`;

const preview: Preview = {
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
};

export default preview;
