import { readdirSync, writeFileSync } from 'fs';
import { resolve } from 'path';

function toCamelCase(input: string): string {
  return input.replace(/-([a-z])/g, (_, char) => char.toUpperCase());
}

const ICONS_DIR = resolve('./assets/icons');
const OUTPUT_FILE = resolve('./assets/react/v3/shared/icons/types.ts');

const files = readdirSync(ICONS_DIR)
  .filter((file) => file.endsWith('.svg'))
  .map((file) => file.replace(/\.svg$/, ''))
  .map(toCamelCase)
  .map((name) => `'${name}'`)
  .sort();

const typeDef = `// This file is auto-generated. Run "npm run generate:icon-types" to generate again.
export const icons = [${files.join(', ')}] as const;

export type IconCollection = (typeof icons)[number];
`;

writeFileSync(OUTPUT_FILE, typeDef);
// eslint-disable-next-line no-console
console.log('✅ icons/types.ts updated.');
