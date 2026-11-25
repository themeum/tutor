import type { Config } from 'svgo';

/**
 * Derive a stable ID namespace from the icon's path inside `assets/icons`,
 * e.g. `assets/icons/kids/clock-2.svg` -> `kids-clock-2`.
 *
 * It depends only on the file location (never on content, order or randomness),
 * so re-running the optimizer always produces the same IDs.
 */
const getIconNamespace = (path: string | undefined): string => {
  const normalized = (path ?? '').replace(/\\/g, '/');
  const relativeName = normalized.match(/assets\/icons\/(.+)\.svg$/i)?.[1];
  const fileName = normalized
    .split('/')
    .pop()
    ?.replace(/\.svg$/i, '');

  return (relativeName ?? fileName ?? 'icon').replace(/[^a-zA-Z0-9_-]+/g, '-');
};

const config: Config = {
  multipass: false,
  js2svg: {
    pretty: false,
  },
  plugins: [
    {
      name: 'preset-default',
    },
    {
      name: 'prefixIds',
      params: {
        delim: '__',
        prefix: (_node, info) => getIconNamespace(info.path),
        prefixClassNames: false,
      },
    },
  ],
};

export default config;
