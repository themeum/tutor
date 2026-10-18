import { Buffer } from 'node:buffer';
import { readdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

import { rspack } from '@rspack/core';

const rootDir = path.dirname(fileURLToPath(import.meta.url));

/** Player entry (`js/tutor-player.js`) and its lazy adapter chunks (`js/lazy-chunks/tutor-player-*.js`). */
const PLAYER_ASSET = /(^|\/)tutor-player(?:-[^/]+)?\.js(\?.*)?$/;
/** Lazy chunks with numeric names are the vendor i18n locale packs (`js/lazy-chunks/6702.js`). */
const LOCALE_CHUNK_ASSET = /(^|\/)lazy-chunks\/\d+\.js(\?.*)?$/;
const LOCALE_MARKER = /i18n[\\/]locales[\\/]/;

let esbuildPromise;

/**
 * Resolves esbuild from the pnpm store: the repo does not depend on it directly, but the toolchain
 * brings one in. The store is probed because a stale `node_modules/.bin` shim cannot be trusted and
 * a partially installed entry has no platform binary.
 */
function loadEsbuild() {
  esbuildPromise ??= (async () => {
    const failures = [];
    let packages = [];

    try {
      packages = (await readdir(path.join(rootDir, 'node_modules/.pnpm'))).filter((name) => /^esbuild@\d/.test(name));
    } catch (error) {
      failures.push(String(error.message));
    }

    for (const name of packages.sort((a, b) => b.localeCompare(a, 'en', { numeric: true }))) {
      try {
        const module = await import(
          pathToFileURL(path.join(rootDir, 'node_modules/.pnpm', name, 'node_modules/esbuild/lib/main.js')).href
        );
        const esbuild = module.default ?? module;

        esbuild.transformSync('let probe = 1', { minify: true });

        return esbuild;
      } catch (error) {
        failures.push(`${name}: ${String(error.message).split('\n')[0]}`);
      }
    }

    throw new Error(`minify-videojs: no working esbuild found\n${failures.join('\n')}`);
  })();

  return esbuildPromise;
}

const toText = (source) => (Buffer.isBuffer(source) ? source.toString('utf8') : String(source));

/**
 * Minifies only the Video.js player assets. The shared SWC minimizer keeps `compress: false` for the
 * WordPress runtime, which leaves the player bundles unminified; this plugin closes that gap for the
 * player entry, its lazy adapter chunks and the vendor i18n locale packs, and touches nothing else.
 */
export class MinifyVideojsAssetsPlugin {
  apply(compiler) {
    compiler.hooks.thisCompilation.tap('MinifyVideojsAssetsPlugin', (compilation) => {
      compilation.hooks.processAssets.tapPromise(
        {
          name: 'MinifyVideojsAssetsPlugin',
          // After the SWC minimizer (PROCESS_ASSETS_STAGE_OPTIMIZE_SIZE), before hashes are computed.
          stage: rspack.Compilation.PROCESS_ASSETS_STAGE_OPTIMIZE_TRANSFER,
        },
        async () => {
          const targets = [];
          for (const asset of compilation.getAssets()) {
            const isPlayer = PLAYER_ASSET.test(asset.name);

            if (!isPlayer && !LOCALE_CHUNK_ASSET.test(asset.name)) {
              continue;
            }

            const text = toText(asset.source.source());

            if (isPlayer || LOCALE_MARKER.test(text)) {
              targets.push({ name: asset.name, text });
            }
          }

          if (targets.length === 0) {
            return;
          }

          const esbuild = await loadEsbuild();

          for (const { name, text } of targets) {
            const { code } = await esbuild.transform(text, { charset: 'utf8', legalComments: 'inline', minify: true });
            compilation.updateAsset(name, new rspack.sources.RawSource(code));
          }

          // eslint-disable-next-line no-console
          console.log(`minify-videojs: minified ${targets.length} assets`);
        },
      );
    });
  }
}
