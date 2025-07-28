import { Global, css } from '@emotion/react';
import { withThemeFromJSXProvider } from '@storybook/addon-themes';
import type { Preview } from '@storybook/react';

import { tutorConfig } from '@TutorShared/config/config';
import { typography } from '@TutorShared/config/typography';

const GlobalStyles = () => (
  <Global
    styles={css`
      *,
      *::before,
      *::after {
        box-sizing: border-box;
      }
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
