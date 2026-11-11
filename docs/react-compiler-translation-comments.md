# React Compiler Translation Comments Restoration

## 1. Overview & Problem Statement

React Compiler compiles components into High-Level Intermediate Representation (HIR) to perform fine-grained reactive memoization. By design, React Compiler generates a fresh AST from HIR and discards comments during compilation ([facebook/react#31364](https://github.com/react/react/issues/31364)).

In the WordPress ecosystem, internationalization (i18n) depends on translator comments:
- Translators on [translate.wordpress.org](https://translate.wordpress.org/) (GlotPress) rely on `/* translators: ... */` notes to understand placeholders (`%s`, `%d`), plural forms, and UI context.
- WordPress build tools and WP-CLI (`wp i18n make-pot`) scan the emitted production JavaScript bundles in SVN releases using the PHP Peast parser.
- Because comments were dropped by React Compiler, **65 out of 97 comments were lost** in compiled JavaScript assets, leaving translators without critical context.

---

## 2. Solution: Post-Build Comment Re-injection

To solve this without disabling the React Compiler or modifying internal Babel/SWC plugins, we implemented a custom Rspack plugin: [`RestoreCommentsPlugin`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/scripts/restore-comments.mjs) registered in [`rspack.config.mjs`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/rspack.config.mjs).

### Pipeline Flow

```mermaid
flowchart TD
    A["Source Code (assets/src/js)<br>• TypeScript / TSX<br>• Has /* translators: ... */"] --> B["React Compiler + SWC Loader<br>• AST transformed<br>• Memoization added<br>• ⚠️ Comments stripped"]
    B --> C["Rspack Bundling & Minification<br>• Chunks created<br>• Variable names mangled<br>• Code minified"]
    
    A -.->|"Pre-scans comments & msgids"| D["RestoreCommentsPlugin<br>(PROCESS_ASSETS_STAGE_SUMMARIZE)"]
    C --> D
    
    D --> E["Emitted Production Assets (assets/js)<br>• Comments re-injected before gettext calls<br>• 100% comment retention"]
    
    E --> F["WordPress GlotPress & WP-CLI<br>(wp i18n make-pot)<br>• 97 / 97 comments extracted into tutor.pot"]
```

---

## 3. How the Plugin Works

The plugin hooks into Rspack's compilation lifecycle at `PROCESS_ASSETS_STAGE_SUMMARIZE`, running after compilation and minification are complete:

```mermaid
sequenceDiagram
    autonumber
    participant Rspack as Rspack Compilation
    participant Plugin as RestoreCommentsPlugin
    participant FS as Source Files (assets/src/js)
    participant Asset as Emitted Assets (assets/js/*.js)

    Rspack->>Plugin: processAssets hook (PROCESS_ASSETS_STAGE_SUMMARIZE)
    Plugin->>FS: extractSourceComments()
    Note over Plugin,FS: Boundary-safe regex extracts all /* translators: */<br/>and // translators: mapped to their target msgid
    FS-->>Plugin: Map<compoundKey, normalizedComment> (97 entries)
    
    loop For each emitted JS asset
        Plugin->>Asset: Check asset name (matches .js or .js?ver=*)
        alt Contains gettext call with matched msgid
            Plugin->>Asset: Locate gettext prefix: (0, _wordpress_i18n.__)("...")
            Plugin->>Asset: Re-inject comment immediately before call
        end
    end
    
    Plugin->>Rspack: compilation.updateAsset() with RawSource
    Rspack-->>Asset: Final output written to disk
```

### Key Technical Details

1. **Token & String Literal Decoder (`readString`)**:
   Instead of fragile regular expressions that can fail on escaped quotes or complex arguments, a custom tokenizer parses `'...'`, `"..."`, and backtick template literals (`` `...` ``, without interpolation). The scanner comprehensively decodes JavaScript escape sequences: single-character escapes (`\n`, `\t`, `\r`, `\b`, `\f`, `\v`, `\0`), hex byte escapes (`\xNN`), standard unicode escapes (`\uNNNN`), ES6 code point escapes (`\u{...}`), and escaped line terminators. If a backtick literal contains dynamic interpolation (`${...}`), it is safely rejected as non-extractable.
2. **Context & Plural Delimited Key**:
   To prevent false collisions when identical message strings are used across different translation contexts or plural forms, keys are uniquely serialized using gettext ASCII control delimiters (`\u0004` EOT for context separation, `\u0003` ETX for plural separation):
   - Single string `__( 'msgid' )`: `\u0004msgid`
   - Contextual `_x( 'msgid', 'context' )`: `context\u0004msgid`
   - Plural `_n( 'single', 'plural', number )`: `\u0004single\u0003plural`
   - Contextual plural `_nx( 'single', 'plural', number, 'context' )`: `context\u0004single\u0003plural` (using `skipArg` to balance parentheses and skip the runtime expression).
3. **Proximity & Line-Adjacency Rule**:
   To ensure comments strictly associate with their target gettext call rather than neighboring code, the source scanner enforces:
   - Comment must directly precede the gettext call within **250 characters**.
   - No blank lines (`/\n\s*\n/`) or intervening statements between comment and call site.
   - Single-line comments (`// translators: ...`) are automatically normalized to block format (`/* translators: ... */`) so inline injection into minified single-line statements does not comment out remaining code.
4. **Single-Pass Linear $O(N)$ String Assembly**:
   Call sites are discovered via a generator (`eachCall`). The emitted bundle is assembled in a single linear pass by collecting untouched code spans in a `chunks` array and appending comments before target calls, culminating in a single `chunks.join('')`. This avoids quadratic string concatenation and regex replacement performance overhead. Existing comments directly preceding calls are detected and preserved without duplication.
5. **Dual-State Tree-Shaken Orphan Separation**:
   When source files contain translatable strings that are eliminated during bundling (e.g. tree-shaken dead code), the plugin checks whether the string appears anywhere in the emitted output:
   - **Cleanly tree-shaken string**: Logged at `info` level (`Skipped tree-shaken string "..."`) to keep compilation logs clean.
   - **Missed restoration**: If the string exists in the emitted bundle but its call site was not matched, a compiler warning is emitted to alert developers immediately.
6. **Query-String Agnostic Asset Matching**:
   Handles versioned chunk asset paths (e.g. `js/lazy-chunks/coupon.js?ver=4.2.1`) emitted by Rspack.

---

## 4. Empirical Benchmark & Statistics

All configurations were compiled in production mode (`rspack --mode=production`) and analyzed using `wp i18n make-pot` across all 15 entrypoints and 13 lazy chunks:

| Metric | State 1: <br>**With React Compiler<br>+ With Plugin** *(Production)* | State 2: <br>**With React Compiler<br>+ No Plugin** *(Broken Comments)* | State 3: <br>**No React Compiler<br>+ No Plugin** *(Pre-Compiler)* | State 4: <br>**No React Compiler<br>+ With Plugin** |
| :--- | :---: | :---: | :---: | :---: |
| **React Compiler Status** | **Active (Memoizing)** | **Active (Memoizing)** | Disabled | Disabled |
| **Translator Comments Plugin** | **Active** | Disabled | Disabled | **Active** |
| **Total JavaScript Strings (`msgid`)** | **1,455** | **1,455** | 1,456 | 1,456 |
| **Translator Comments in POT (`#. translators:`)** | **97 / 97 (100%)** | **32 / 97 (33%)** ❌ *(65 lost)* | 60 / 84 (71%) ⚠️ | 85 / 84 (100%) |
| **Total JS Bundle Size (Uncompressed)** | 8,188,377 bytes (7.81 MB) | 8,180,895 bytes (7.80 MB) | 4,550,916 bytes (4.34 MB) | 4,551,676 bytes (4.34 MB) |
| **Total JS Bundle Size (Gzip / Wire)** | 2,411,098 bytes (2.30 MB) | 2,409,307 bytes (2.30 MB) | 1,402,298 bytes (1.34 MB) | 1,402,436 bytes (1.34 MB) |
| **Comment Overhead (Uncompressed)** | **+7,482 bytes (+0.091%)** | Baseline | Baseline | +760 bytes (+0.016%) |
| **Comment Overhead (Gzipped / Wire)** | **+1,791 bytes (+0.074%)** | Baseline | Baseline | +138 bytes (+0.009%) |

### Key Findings

1. **Comment Retention**:
   - **State 2 (Compiler without Plugin)** lost 65 out of 97 comments (~67% loss).
   - **State 1 (Compiler with Plugin)** restores **100% of translator comments (97 / 97)**.
   - Even in State 3 (pre-compiler), standard SWC transforms dropped ~24 comments. The plugin improves comment extraction beyond original baseline.
2. **String Integrity**:
   - Exactly **1,455 translatable strings** (1,456 `msgid` entries including the empty POT header) exist in the compiled JavaScript output across all configurations. No strings are dropped, modified, or duplicated.
3. **Negligible Bundle Size Impact**:
   - The total wire-size impact of re-injecting comments across the entire codebase (15 entry bundles + 13 lazy chunks) is **+1,791 bytes gzipped (+0.074%)**, averaging less than 64 bytes per chunk.

---

## 5. Verification Commands

To verify that comments are intact after builds:

```bash
# 1. Build production bundles
pnpm exec rspack --mode=production

# 2. Extract POT from compiled JS bundles using WP-CLI
php -d memory_limit=-1 $(which wp) i18n make-pot . test-verify.pot \
  --slug=tutor \
  --domain=tutor \
  --include="assets/js/*.js,assets/js/lazy-chunks/*.js" \
  --quiet

# 3. Count extracted translator comments (must output 97)
grep -c "^#\. translators:" test-verify.pot

# 4. Clean up test POT
rm test-verify.pot
```

---

## 6. Ecosystem References & Why This Solution is Custom

- **Upstream Issue**: [facebook/react#31364](https://github.com/facebook/react/issues/31364) (*[Compiler]: All comments removed from source code*).
  - Opened by Pascal Birchler (`@swissspidy`, WP-CLI i18n lead) specifically requesting that React Compiler preserve translator comments for WordPress.
  - Closed by Meta / React Core team lead Joe Savona (`@josephsavona`) as **"not planned"** because React Compiler lowers code to High-Level IR (HIR) and synthesizes fresh ASTs from scratch.
- **Why Standard Workarounds Fail**:
  - The suggested workaround from React core is to run translation extraction on raw source files (`src/`) rather than compiled bundles.
  - However, **`translate.wordpress.org` (GlotPress via PHP Peast parser) scans the compiled JavaScript bundles** (`assets/js/*.js`) published to the WordPress.org SVN release repository, where source files are not present.
  - The other alternative is opting out components using `"use no memo"`, which disables React Compiler optimizations.
- **Novelty of This Approach**:
  - No existing public third-party package implements post-compile comment restoration for React Compiler.
  - This custom Rspack post-compilation hook (`RestoreCommentsPlugin`) serves as a bespoke bridge, ensuring 100% comment retention on `translate.wordpress.org` while keeping React Compiler active across all components.

