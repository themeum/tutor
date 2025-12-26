import { exec } from 'child_process';
import { readdirSync, writeFileSync } from 'fs';
import { resolve } from 'path';

const ICONS_DIR = resolve('./assets/icons');
const TS_OUTPUT_FILE = resolve('./assets/src/js/v3/shared/icons/types.ts');
const PHP_OUTPUT_FILE = resolve('./classes/Icon.php');

const iconNames = readdirSync(ICONS_DIR)
  .filter((file) => file.endsWith('.svg'))
  .map((file) => file.replace(/\.svg$/, ''));

// ------------------------
// TypeScript Output
// ------------------------
const tsNames = iconNames
  .map((name) => name.replace(/-([a-z])/g, (_, char) => char.toUpperCase()).replace(/-(\d)/g, '$1'))
  .map((name) => `  '${name}'`)
  .sort()
  .join(',\n');

const tsContent = `// This file is auto-generated. Run "npm run generate:icon-types" to generate again.
export const icons = [\n${tsNames},\n] as const;

export type IconCollection = (typeof icons)[number];
`;

writeFileSync(TS_OUTPUT_FILE, tsContent);
// eslint-disable-next-line no-console
console.log('✅ TypeScript icons/types.ts updated.');

// ------------------------
// PHP Class Output
// ------------------------
const phpNames = iconNames
  .map((name) => `    const ${name.replaceAll('-', '_').toUpperCase()} = '${name}';`)
  .sort()
  .join('\n');

const phpClass = `<?php
/**
 * This file is auto-generated. Run "npm run generate:icon-types" to generate again.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.7.0
 */

namespace TUTOR;

/**
 * Icon class for Icon Names.
 *
 * @since 3.7.0
 */
final class Icon {
${phpNames}
}
`;

writeFileSync(PHP_OUTPUT_FILE, phpClass);
exec('phpcbf classes/Icon.php');
exec('phpcbfwp classes/Icon.php');

// eslint-disable-next-line no-console
console.log('✅ PHP Icon class generated.');
