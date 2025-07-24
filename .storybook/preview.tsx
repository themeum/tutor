import type { Preview } from '@storybook/react';
import { tutorConfig } from '../assets/react/v3/shared/config/config';

import { Global, css } from '@emotion/react';
import { withThemeFromJSXProvider } from '@storybook/addon-themes';
import { typography } from '../assets/react/v3/shared/config/typography';

const GlobalStyles = () => (
  <Global
    styles={css`
      body {
        ${typography.body()}
      }
    `}
  />
);

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

  decorators: [
    withThemeFromJSXProvider({
      GlobalStyles,
    }),
  ],
};

export default preview;
