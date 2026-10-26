/* eslint-disable no-console */

/**
 * Scans one or more directories for SVG files and replaces hex color values
 * with CSS custom properties, using the Tutor visual token map.
 *
 * All paths are resolved relative to the project root (where package.json
 * lives), regardless of where this script is located.
 *
 * Usage (via package.json script):
 *   tsx assets/core/ts/utils/replace-hex-colors.ts [options] <dir> [dir2 ...]
 *
 * Options:
 *   --dry-run        Preview changes without writing files
 *   --mapping <file> JSON file to override/extend the built-in color map
 *   --exts <list>    Comma-separated extensions to scan (default: .svg)
 *   --no-vars-block  Skip the :root { } summary at the end
 */

import fs from 'fs';
import path from 'path';

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type ColorMap = Record<string, string>; // { "#hex": "--var-name" }

interface ProcessResult {
  changed: boolean;
  original: string;
  updated: string;
}

interface DirSummary {
  dir: string;
  total: number;
  modified: number;
}

// ---------------------------------------------------------------------------
// Resolve project root — walk up from __dirname until we find package.json
// ---------------------------------------------------------------------------

const findProjectRoot = (startDir: string): string => {
  let dir = startDir;
  while (true) {
    if (fs.existsSync(path.join(dir, 'package.json'))) return dir;
    const parent = path.dirname(dir);
    if (parent === dir) return process.cwd(); // filesystem root fallback
    dir = parent;
  }
};

const PROJECT_ROOT = findProjectRoot(__dirname);

// ---------------------------------------------------------------------------
// CLI argument parsing
// ---------------------------------------------------------------------------

const args = process.argv.slice(2);

if (!args.length || args.includes('--help')) {
  console.log(
    [
      '',
      'Usage: tsx assets/core/ts/utils/replace-hex-colors.ts [options] <dir> [dir2 ...]',
      '',
      'Options:',
      '  --dry-run          Preview changes, do not write files',
      '  --mapping <file>   JSON file { "#hex": "--var-name" } to override/extend defaults',
      '  --exts <list>      Comma-separated extensions (default: .svg)',
      '  --no-vars-block    Skip the :root { } summary',
      '',
      `Paths are relative to the project root: ${PROJECT_ROOT}`,
      '',
    ].join('\n'),
  );
  process.exit(0);
}

const dryRun = args.includes('--dry-run');
const noVarsBlock = args.includes('--no-vars-block');

const getFlag = (name: string): string | null => {
  const idx = args.indexOf(name);
  return idx !== -1 ? (args[idx + 1] ?? null) : null;
};

const extArg = getFlag('--exts');
const extensions = extArg ? extArg.split(',').map((e) => e.trim()) : ['.svg'];
const mappingFile = getFlag('--mapping');

// Collect positional arguments (skip flags and their values)
const FLAGS_WITH_VALUES = new Set(['--mapping', '--exts']);
const targetDirs: string[] = [];

for (let i = 0; i < args.length; i++) {
  if (args[i].startsWith('--')) {
    if (FLAGS_WITH_VALUES.has(args[i])) i++; // skip flag value
    continue;
  }
  targetDirs.push(args[i]);
}

if (!targetDirs.length) {
  console.error('Error: at least one target directory is required.');
  process.exit(1);
}

// ---------------------------------------------------------------------------
// Color map — sourced from UrlHelper::get_default_color_map() (keys flipped)
// ---------------------------------------------------------------------------

const DEFAULT_COLOR_MAP: ColorMap = {
  '#ffffff': '--tutor-visual-gray-1',
  '#ececed': '--tutor-visual-gray-2',
  '#cecfd2': '--tutor-visual-gray-3',
  '#2d3039': '--tutor-visual-gray-4',
  '#4979e8': '--tutor-visual-brand-1',
  '#a4bcf4': '--tutor-visual-brand-2',
  '#dbe4fa': '--tutor-visual-brand-3',
  '#28a745': '--tutor-visual-success-1',
  '#1d0d0c': '--tutor-visual-critical-1',
  '#fee4e2': '--tutor-visual-critical-2',
  '#fde272': '--tutor-visual-caution-1',
  '#a15c07': '--tutor-visual-caution-2',
  '#542c0d': '--tutor-visual-caution-3',
  '#ff8904': '--tutor-visual-orange-1',
  '#cbfd78': '--tutor-visual-exception-1',
  '#f4f433': '--tutor-visual-exception-2',
  '#ede9fe': '--tutor-visual-exception-3',
};

const loadColorMap = (): ColorMap => {
  let map: ColorMap = { ...DEFAULT_COLOR_MAP };

  if (!mappingFile) return map;

  const mappingPath = path.resolve(PROJECT_ROOT, mappingFile);

  try {
    const parsed = JSON.parse(fs.readFileSync(mappingPath, 'utf8')) as ColorMap;
    const normalised = Object.fromEntries(Object.entries(parsed).map(([k, v]) => [k.toLowerCase(), v]));
    map = { ...map, ...normalised };
    console.log(`Merged ${Object.keys(parsed).length} override(s) from "${mappingFile}"`);
  } catch (err) {
    console.error(`Failed to read mapping file "${mappingPath}": ${(err as Error).message}`);
    process.exit(1);
  }

  return map;
};

const colorMap = loadColorMap();

// ---------------------------------------------------------------------------
// Hex utilities
// ---------------------------------------------------------------------------

// Matches 3- and 6-digit hex colours.
const HEX_RE = /(?<![a-f0-9#])#([0-9a-f]{6}|[0-9a-f]{3})\b/gi;

const normaliseHex = (hex: string): string => {
  const raw = hex.slice(1).toLowerCase();
  if (raw.length === 3) {
    return '#' + raw[0] + raw[0] + raw[1] + raw[1] + raw[2] + raw[2];
  }
  return '#' + raw;
};

// ---------------------------------------------------------------------------
// Directory walker
// ---------------------------------------------------------------------------

const walkDir = (dir: string, exts: string[]): string[] => {
  const results: string[] = [];

  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name.startsWith('.') || entry.name === 'node_modules') continue;

    const fullPath = path.join(dir, entry.name);

    if (entry.isDirectory()) {
      results.push(...walkDir(fullPath, exts));
    } else if (exts.includes(path.extname(entry.name))) {
      results.push(fullPath);
    }
  }

  return results;
};

// ---------------------------------------------------------------------------
// Core replacement
// ---------------------------------------------------------------------------

interface UnmappedEntry {
  file: string; // path relative to PROJECT_ROOT
  line: number;
  hex: string; // normalised 6-digit form
}

const unmappedEntries: UnmappedEntry[] = [];
const colorRegistry = new Map<string, string>(); // accumulated across all dirs

const processFile = (filePath: string): ProcessResult => {
  const original = fs.readFileSync(filePath, 'utf8');
  const lines = original.split('\n');
  let changed = false;

  // Build character-offset index for line-number lookup.
  const lineOffsets: number[] = [];
  let offset = 0;
  for (const line of lines) {
    lineOffsets.push(offset);
    offset += line.length + 1; // +1 for the \n
  }

  const rel = path.relative(PROJECT_ROOT, filePath);

  const updated = original.replace(HEX_RE, (match, _hex, matchOffset: number) => {
    // Skip hex values that are already sitting inside a var() fallback
    // e.g. var(--some-var, #ececed) — the #ececed should not be re-wrapped.
    const before = original.slice(Math.max(0, matchOffset - 10), matchOffset);
    if (/,\s*$/.test(before)) return match;

    const norm = normaliseHex(match);
    const varName = colorMap[norm];

    if (!varName) {
      // Binary-search lineOffsets to find which line this match is on.
      let lo = 0,
        hi = lineOffsets.length - 1;
      while (lo < hi) {
        const mid = (lo + hi + 1) >> 1;
        if (lineOffsets[mid] <= matchOffset) lo = mid;
        else hi = mid - 1;
      }
      unmappedEntries.push({ file: rel, line: lo + 1, hex: norm });
      return match;
    }

    colorRegistry.set(norm, varName);
    changed = true;
    // Always use the normalised 6-digit form as the fallback so that
    // #fff and #ffffff in source files both produce the same output.
    return `var(${varName}, ${norm})`;
  });

  return { changed, original, updated };
};

// ---------------------------------------------------------------------------
// :root block
// ---------------------------------------------------------------------------

const generateVarsBlock = (registry: Map<string, string>): string => {
  const lines = [':root {'];

  for (const [hex, varName] of [...registry.entries()].sort((a, b) => a[1].localeCompare(b[1]))) {
    lines.push(`  ${varName}: ${hex};`);
  }

  lines.push('}');
  return lines.join('\n');
};

// ---------------------------------------------------------------------------
// Process a single directory
// ---------------------------------------------------------------------------

const processDir = (rawDir: string): DirSummary => {
  const absDir = path.resolve(PROJECT_ROOT, rawDir);

  if (!fs.existsSync(absDir)) {
    console.warn(`⚠  Directory not found, skipping: ${rawDir}`);
    return { dir: rawDir, total: 0, modified: 0 };
  }

  const files = walkDir(absDir, extensions);
  console.log(`📂 ${rawDir}  (${files.length} file(s))`);

  let modified = 0;

  for (const filePath of files) {
    const { changed, original, updated } = processFile(filePath);
    if (!changed) continue;

    modified++;
    const rel = path.relative(PROJECT_ROOT, filePath);
    console.log(`   ✏  ${rel}`);

    if (dryRun) {
      const origLines = original.split('\n');
      const updtLines = updated.split('\n');
      for (let i = 0; i < origLines.length; i++) {
        if (origLines[i] !== updtLines[i]) {
          console.log(`        line ${i + 1}:`);
          console.log(`          - ${origLines[i].trim()}`);
          console.log(`          + ${updtLines[i].trim()}`);
        }
      }
    } else {
      fs.writeFileSync(filePath, updated, 'utf8');
    }
  }

  return { dir: rawDir, total: files.length, modified };
};

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

const main = (): void => {
  console.log(`\nProject root: ${PROJECT_ROOT}`);
  console.log(`Color map:    ${Object.keys(colorMap).length} entries`);
  console.log(`Dry run:      ${dryRun ? 'yes' : 'no'}\n`);

  const summaries = targetDirs.map(processDir);

  const totalFiles = summaries.reduce((n, s) => n + s.total, 0);
  const totalModified = summaries.reduce((n, s) => n + s.modified, 0);
  const dirWord = targetDirs.length === 1 ? 'directory' : 'directories';

  console.log(
    `\n${dryRun ? '[DRY RUN] ' : ''}Modified ${totalModified} / ${totalFiles} file(s) across ${targetDirs.length} ${dirWord}`,
  );

  if (unmappedEntries.length) {
    // Group by hex so each unknown colour is reported once with all its locations.
    const byHex = new Map<string, UnmappedEntry[]>();
    for (const entry of unmappedEntries) {
      const list = byHex.get(entry.hex) ?? [];
      list.push(entry);
      byHex.set(entry.hex, list);
    }

    console.log(`\n❌ ${byHex.size} unmapped colour(s) — left unchanged:`);
    for (const [hex, entries] of [...byHex.entries()].sort()) {
      console.log(`\n   ${hex}`);
      for (const { file, line } of entries) {
        console.log(`     ${file}:${line}`);
      }
    }
    console.log(`\n   Add them via --mapping or extend DEFAULT_COLOR_MAP in the script.`);
  }

  if (colorRegistry.size && !noVarsBlock) {
    console.log('\n── :root block (for reference) ──────────────────────────────');
    console.log(generateVarsBlock(colorRegistry));
    console.log('─────────────────────────────────────────────────────────────\n');
  }
};

main();
