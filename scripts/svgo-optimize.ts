import { execFileSync } from 'child_process';
import { readFileSync } from 'fs';
import { globSync } from 'fs';
import { dirname, resolve } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const SVGO_IGNORE = '<!-- svgo ignore -->';
const CONFIG = resolve(__dirname, '../svgo.config.ts');

// Called from lint-staged (files passed as args)
// Called from npm script (no args, scan folder)
let files: string[] = process.argv.slice(2);

if (files.length === 0) {
  // npm run svgo — scan the folder
  files = globSync('assets/icons/**/*.svg');
}

const filtered = files.filter((f) => {
  const content = readFileSync(f, 'utf-8');
  return !content.includes(SVGO_IGNORE);
});

if (filtered.length === 0) process.exit(0);

execFileSync('svgo', ['--config', CONFIG, ...filtered], { stdio: 'inherit' });
