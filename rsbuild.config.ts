// This is used only for storybook to configure the build process, it will not affect tutor build process.

import { defineConfig } from '@rsbuild/core';
import { pluginReact } from '@rsbuild/plugin-react';

export default defineConfig({
  plugins: [pluginReact()],
});
