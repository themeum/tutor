import type { Config } from 'svgo';

const config: Config = {
  multipass: false,
  js2svg: {
    pretty: false,
  },
  plugins: [
    {
      name: 'preset-default',
    },
  ],
};

export default config;
