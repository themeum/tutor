/**
 * Restore Comments Plugin for Rspack
 *
 * Context & Rationale:
 * - Upstream Issue: https://github.com/facebook/react/issues/31364
 *   Opened by Pascal Birchler (@swissspidy, WP-CLI i18n lead) reporting that React Compiler
 *   strips all translator comments from compiled code. Meta closed the issue as "not planned"
 *   because the compiler lowers to High-Level IR (HIR) and synthesizes fresh ASTs without comments.
 * - Why Standard Workarounds Fail for WordPress Plugins:
 *   The official suggestion is to run `make-pot` on uncompiled source files (`src/`), or add `"use no memo"`
 *   to components. However, translate.wordpress.org (GlotPress via PHP Peast parser) scans the final COMPILED
 *   JavaScript bundles committed to the WordPress.org SVN repository (`assets/js/`).
 * - Solution:
 *   No public third-party package currently solves this. This custom Rspack post-compilation hook
 *   (`PROCESS_ASSETS_STAGE_SUMMARIZE`) bridges the gap by extracting `translators:` comments from source
 *   and re-injecting them into the emitted minified bundles before gettext calls. Restores comments for
 *   literal gettext calls and warns on the rest while leaving React Compiler fully enabled.
 */

import fs from 'node:fs';

import { rspack } from '@rspack/core';

const FN = '__|_n|_x|_nx';
// Matches minified calls like `return(0, i.__)(` or `(0, i.__)(` and bare names like `__(` or `i18n.__(`.
const CALL = new RegExp(
  String.raw`\(\s*0\s*,\s*[\w$]+(?:\.[\w$]+)*\.(${FN})\s*\)\s*\(|(?<![\w$])(?:[\w$]+\.)*(${FN})\s*\(`,
  'g',
);
const TRANSLATORS =
  /\/\*(?:(?!\*\/)[\s\S])*?translators:(?:(?!\*\/)[\s\S])*?\*\/|(?<![:\w"'`])\/\/[^\n\r]*?translators:[^\n\r]*/gi;

const skipWs = (s, i) => {
  while (i < s.length && /\s/.test(s[i])) i++;
  return i;
};

/** Reads a '...', "...", or `...` literal at i (after whitespace) and returns the decoded value. */
const readString = (s, i) => {
  i = skipWs(s, i);
  const q = s[i];
  if (q !== '"' && q !== "'" && q !== '`') return null;
  let out = '';
  for (i++; i < s.length; i++) {
    const c = s[i];
    if (c === q) return { value: out, end: i + 1 };
    if (q === '`' && c === '$' && s[i + 1] === '{') return null;
    if (q !== '`' && (c === '\n' || c === '\r')) return null;
    if (c !== '\\') {
      out += c;
      continue;
    }
    const n = s[++i];
    const simple = { n: '\n', t: '\t', r: '\r', b: '\b', f: '\f', v: '\v', 0: '\0' };
    if (n in simple) out += simple[n];
    else if (n === 'x') out += String.fromCharCode(parseInt(s.slice(i + 1, (i += 2) + 1), 16));
    else if (n === 'u' && s[i + 1] === '{') {
      const close = s.indexOf('}', i);
      out += String.fromCodePoint(parseInt(s.slice(i + 2, close), 16));
      i = close;
    } else if (n === 'u') out += String.fromCharCode(parseInt(s.slice(i + 1, (i += 4) + 1), 16));
    else if (n === '\n') continue;
    else out += n;
  }
  return null;
};

/** Skips one argument expression (for the non-literal `number` arg of _n/_nx). Returns index of the next top-level ','. */
const skipArg = (s, i) => {
  let depth = 0;
  for (; i < s.length; i++) {
    const c = s[i];
    if (c === '"' || c === "'" || c === '`') {
      const r = readString(s, i);
      if (!r) return -1;
      i = r.end - 1;
    } else if ('([{'.includes(c)) depth++;
    else if (')]}'.includes(c)) {
      if (depth === 0) return -1;
      depth--;
    } else if (c === ',' && depth === 0) return i;
  }
  return -1;
};

const expectComma = (s, i) => {
  i = skipWs(s, i);
  return s[i] === ',' ? i + 1 : -1;
};

/** Builds a decoded key from the args that follow `fn(`. Returns null for non-literal calls. */
const readKey = (s, i, fn) => {
  const a = readString(s, i);
  if (!a) return null;
  if (fn === '__') return `\u0004${a.value}`;
  let p = expectComma(s, a.end);
  if (p < 0) return null;
  if (fn === '_x') {
    const ctx = readString(s, p);
    return ctx ? `${ctx.value}\u0004${a.value}` : null;
  }
  // _n(single, plural, number, ...) and _nx(single, plural, number, context, ...)
  const plural = readString(s, p);
  if (!plural) return null;
  if (fn === '_n') return `\u0004${a.value}\u0003${plural.value}`;
  p = expectComma(s, plural.end);
  if (p < 0) return null;
  p = skipArg(s, p);
  if (p < 0) return null;
  const ctx = readString(s, p + 1);
  return ctx ? `${ctx.value}\u0004${a.value}\u0003${plural.value}` : null;
};

const eachCall = function* (code, from = 0) {
  CALL.lastIndex = from;
  let m;
  while ((m = CALL.exec(code))) {
    const key = readKey(code, m.index + m[0].length, m[1] || m[2]);
    if (key) yield { key, start: m.index, end: m.index + m[0].length };
  }
};

const fileCache = new Map();

const extractSourceComments = (files) => {
  const map = new Map();
  for (const file of files) {
    let stat;
    try {
      stat = fs.statSync(file);
    } catch {
      continue;
    }

    let fileMap;
    const cached = fileCache.get(file);
    if (cached && cached.mtimeMs === stat.mtimeMs) {
      fileMap = cached.fileMap;
    } else {
      fileMap = new Map();
      let code;
      try {
        code = fs.readFileSync(file, 'utf8');
      } catch {
        continue;
      }
      if (/translators:/i.test(code)) {
        for (const m of code.matchAll(TRANSLATORS)) {
          const commentEnd = m.index + m[0].length;
          const call = eachCall(code, commentEnd).next().value;
          if (!call) continue;
          const gap = code.slice(commentEnd, call.start);
          if (gap.length > 250 || /\n\s*\n/.test(gap) || fileMap.has(call.key)) continue;
          const raw = m[0].trim();
          fileMap.set(call.key, raw.startsWith('//') ? `/* ${raw.replace(/^\/\/\s*/, '')} */` : raw);
        }
      }
      fileCache.set(file, { mtimeMs: stat.mtimeMs, fileMap });
    }

    for (const [key, comment] of fileMap.entries()) {
      if (!map.has(key)) map.set(key, comment);
    }
  }
  return map;
};

/** Collapse newlines to prevent ASI when comments follow return, throw, yield, or break */
const oneLine = (c) => c.replace(/\s*[\r\n]+\s*/g, ' ');

/** Single pass over one bundle. Returns { code, restored } */
const restore = (code, commentMap, restored) => {
  const chunks = [];
  let last = 0;
  for (const { key, start } of eachCall(code)) {
    const comment = commentMap.get(key);
    if (!comment) continue;
    restored.add(key);
    // Skip if a translators comment sits directly before this call
    const before = code.slice(Math.max(0, start - 600), start).trimEnd();
    if (before.endsWith('*/') && /translators:/i.test(before.slice(before.lastIndexOf('/*')))) continue;
    chunks.push(code.slice(last, start), oneLine(comment), ' ');
    last = start;
  }
  if (!chunks.length) return null;
  chunks.push(code.slice(last));
  return chunks.join('');
};

export class RestoreCommentsPlugin {
  apply = (compiler) => {
    compiler.hooks.compilation.tap('RestoreCommentsPlugin', (compilation) => {
      compilation.hooks.processAssets.tap(
        { name: 'RestoreCommentsPlugin', stage: rspack.Compilation.PROCESS_ASSETS_STAGE_SUMMARIZE },
        (assets) => {
          const files = [...compilation.fileDependencies].filter(
            (f) => /\.[jt]sx?$/.test(f) && !f.endsWith('.d.ts') && !f.includes('node_modules'),
          );
          const commentMap = extractSourceComments(files);
          if (!commentMap.size) return;

          const restored = new Set();
          const outputs = [];

          for (const name of Object.keys(assets)) {
            if (!/\.js(\?.*)?$/i.test(name)) continue;
            const asset = compilation.getAsset(name);
            if (!asset?.source) continue;
            const sourceStr = asset.source.source().toString();
            const next = restore(sourceStr, commentMap, restored);
            if (next) {
              compilation.updateAsset(name, new rspack.sources.RawSource(next));
              outputs.push(next);
            } else {
              outputs.push(sourceStr);
            }
          }

          for (const key of commentMap.keys()) {
            if (restored.has(key)) continue;
            const [ctx, rest] = key.split('\u0004');
            const id = rest.split('\u0003')[0];
            const risky = /[\\"'\n\r\t\u0080-\uffff]/.test(id);
            const variants = [JSON.stringify(id), `'${id.replace(/'/g, "\\'")}'`];
            const present = risky || outputs.some((c) => variants.some((v) => c.includes(v)));
            if (present) {
              compilation.warnings.push(
                new Error(
                  `RestoreCommentsPlugin: translator comment not restored for "${id}"${ctx ? ` (ctx: ${ctx})` : ''}`,
                ),
              );
            } else {
              compilation.getLogger('RestoreCommentsPlugin').debug(`Skipped tree-shaken string "${id}"`);
            }
          }
        },
      );
    });
  };
}
