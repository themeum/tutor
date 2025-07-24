const { createReadStream, createWriteStream, mkdirSync, readFileSync, readdirSync, rmSync, statSync } = require('fs');
const { dirname, extname, join, relative } = require('path');
const { pipeline } = require('stream/promises');
const archiver = require('archiver');

// Streamlined color palette with only necessary colors
const colors = {
  // Base formatting
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  dim: '\x1b[2m',

  // Primary colors - using a focused, pleasing palette
  teal: '\x1b[38;5;43m', // Vibrant teal for primary elements
  green: '\x1b[38;5;114m', // Soft green for success messages
  red: '\x1b[38;5;203m', // Soft red for errors
  blue: '\x1b[38;5;75m', // Sky blue for information
  yellow: '\x1b[38;5;221m', // Warm yellow for warnings/spinners
  orange: '\x1b[38;5;209m', // Warm orange for warnings
  white: '\x1b[37m', // White for standard text

  // Semantic mappings (aliases for better code readability)
  success: '\x1b[38;5;114m', // Green
  error: '\x1b[38;5;203m', // Red
  warning: '\x1b[38;5;209m', // Orange
  info: '\x1b[38;5;75m', // Blue
  highlight: '\x1b[38;5;221m', // Yellow

  // Header colors
  headerPrimary: '\x1b[38;5;43m', // Teal
  headerSuccess: '\x1b[38;5;114m', // Green
  headerError: '\x1b[38;5;203m', // Red
  headerMagenta: '\x1b[38;5;177m', // Softer magenta
};

const symbols = {
  success: '✅',
  error: '❌',
  warning: '⚠️',
  info: '💡',
  rocket: '🚀',
  package: '📦',
  file: '📄',
  folder: '📁',
  clock: '⏱️',
  arrow: '➡️',
  bullet: '•',
  spinner: ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
};

// Helper to get the visual width of a string (accounting for emojis)
const getVisualWidth = (str) => {
  // Remove ANSI escape codes first
  const cleanStr = str.replace(/\x1b\[[0-9;]*m/g, '');

  // Count characters but account for specific emojis we use
  let width = 0;
  let i = 0;
  while (i < cleanStr.length) {
    const char = cleanStr[i];

    // Check for specific emojis that take 2 terminal columns
    if (
      char === '✅' ||
      char === '❌' ||
      char === '⚠️' ||
      char === '💡' ||
      char === '🚀' ||
      char === '📦' ||
      char === '📄' ||
      char === '📁' ||
      char === '⏱️' ||
      char === '➡️'
    ) {
      width += 2; // These emojis take 2 columns
      i++;
    } else if (char === '️' && i > 0) {
      // Variation selector - doesn't add width
      i++;
    } else {
      width += 1; // Regular characters take 1 column
      i++;
    }
  }

  return width;
};

// Helper functions for creating stylized text with fixed width
const style = {
  // Create fixed-width box with consistent dimensions
  box: (text, boxColor = colors.headerPrimary) => {
    const boxWidth = 50; // Fixed width for all boxes
    const lines = text.split('\n');

    // Create the formatted box with proper padding
    const formattedBox = [`\n${boxColor}${colors.bright}╔${'═'.repeat(boxWidth)}╗${colors.reset}`];

    // Add each line with centered content
    for (const line of lines) {
      const visualWidth = getVisualWidth(line);
      const totalPadding = boxWidth - visualWidth;
      const leftPadding = Math.max(0, Math.floor(totalPadding / 2));
      const rightPadding = Math.max(0, totalPadding - leftPadding);

      formattedBox.push(
        `${boxColor}${colors.bright}║${' '.repeat(leftPadding)}${line}${' '.repeat(rightPadding)}║${colors.reset}`,
      );
    }

    formattedBox.push(`${boxColor}${colors.bright}╚${'═'.repeat(boxWidth)}╝${colors.reset}\n`);

    return formattedBox.join('\n');
  },
};

const logSuccess = (message) => {
  console.log(`${colors.success}${symbols.success} ${message}${colors.reset}`);
};

const logError = (message) => {
  console.log(`${colors.error}${symbols.error} ${message}${colors.reset}`);
};

const logWarning = (message) => {
  console.log(`${colors.warning}${symbols.warning} ${message}${colors.reset}`);
};

const logInfo = (message) => {
  console.log(`${colors.info}${symbols.info} ${message}${colors.reset}`);
};

const logStep = (step, message) => {
  console.log(`\n${colors.blue}${colors.bright}[${step}]${colors.reset} ${colors.white}${message}${colors.reset}`);
};

const createSpinner = (message) => {
  let index = 0;
  const interval = setInterval(() => {
    process.stdout.write(`\r${colors.yellow}${symbols.spinner[index]} ${message}${colors.reset}`);
    index = (index + 1) % symbols.spinner.length;
  }, 100);

  return {
    stop: (successMessage) => {
      clearInterval(interval);
      process.stdout.write(`\r${colors.success}${symbols.success} ${successMessage || message}${colors.reset}\n`);
    },
    fail: (errorMessage) => {
      clearInterval(interval);
      process.stdout.write(`\r${colors.error}${symbols.error} ${errorMessage || message}${colors.reset}\n`);
    },
  };
};

// Helper function for formatting bytes
const formatBytes = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`;
};

const printHeader = () => {
  console.log(style.box(`${symbols.rocket} TUTOR BUILD PROCESS ${symbols.package}`, colors.headerMagenta));
};

const printFooter = (duration) => {
  console.log(style.box(`✅ BUILD COMPLETED SUCCESSFULLY!\n⏱️ Duration: ${duration}`, colors.headerSuccess));
};

const CONFIG = {
  sourceDir: '.',
  buildDir: './build',
  excludePatterns: [
    '*.json',
    './build/**',
    'assets/*.min.css',
    'assets/*.min.css.map',
    'assets/**/*.map',
    'assets/react/**',
    'assets/scss/**',
    'assets/css/fonts/**',
    'assets/css/images/**',
    'assets/.sass-cache',
    'node_modules/**',
    'v2-library/**',
    'test/**',
    '.docz/**',
    '*.zip',
    '.github',
    '.vscode',
    'readme.md',
    '.DS_Store',
    '**/.DS_Store',
    'LICENSE',
    '*.lock',
    '*.mjs',
    'yarn-error.log',
    'bin/**',
    'tests/**',
    '.env',
    'vendor/bin/**',
    'vendor/doctrine/**',
    'vendor/myclabs/**',
    'vendor/nikic/**',
    'vendor/phar-io/**',
    'vendor/phpdocumentor/**',
    'vendor/phpspec/**',
    'vendor/phpunit/**',
    'vendor/sebastian/**',
    'vendor/theseer/**',
    'vendor/webmozart/**',
    'vendor/yoast/**',
    '.phpunit.result.cache',
    '*.yml',
    '*.yaml',
    'phpunit.xml.dist',
    'phpunit.xml',
    'phpcs.xml',
    'phpcs.xml.dist',
    'tutor-droip/**',
    'includes/droip/**',
    'cypress/**',
    'cypress.config.ts',
    '.husky/**',
    '.lintstagedrc',
    'build.js',
  ],
};

let versionNumber = '';

const extractVersionNumber = () => {
  const spinner = createSpinner('Extracting version number from tutor.php...');

  try {
    const tutorPhpContent = readFileSync('tutor.php', 'utf8');
    versionNumber = tutorPhpContent.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';

    if (!versionNumber) {
      spinner.fail('Failed to extract version number');
      throw new Error('Could not extract version number from tutor.php');
    }

    spinner.stop(`Version detected: ${colors.bright}${versionNumber}${colors.reset}`);
  } catch (error) {
    spinner.fail('Error reading tutor.php');
    logError(`Error reading version: ${error.message}`);
    process.exit(1);
  }
};

/**
 * Determines if a file should be excluded from the build
 * @param {string} filePath - Relative path of the file to check
 * @returns {boolean} - True if the file should be excluded
 */
const shouldExcludeFile = (filePath) => {
  // Exclude files/folders starting with a dot (.)
  const pathSegments = filePath.split('/');
  if (pathSegments.some((segment) => segment.startsWith('.'))) {
    return true;
  }

  // Extract critical paths from exclusion patterns for early returns
  const criticalPathsFromPatterns = CONFIG.excludePatterns
    .filter((pattern) => !pattern.includes('*') && !pattern.startsWith('.'))
    .map((pattern) => pattern.replace(/\/\*\*$/, ''));

  // Add base directories from wildcard patterns
  CONFIG.excludePatterns
    .filter((pattern) => pattern.endsWith('/**'))
    .forEach((pattern) => {
      const basePath = pattern.replace(/\/\*\*$/, '');
      if (!criticalPathsFromPatterns.includes(basePath)) {
        criticalPathsFromPatterns.push(basePath);
      }
    });

  // Early return for critical paths
  if (criticalPathsFromPatterns.some((path) => filePath.includes(path))) {
    return true;
  }

  // Continue with pattern matching
  return CONFIG.excludePatterns.some((pattern) => {
    // Skip dot files as we've already handled them above
    if (pattern.startsWith('.') && !pattern.includes('/') && !pattern.includes('*')) {
      return false;
    }

    // Handle patterns with wildcards
    const normalizedPattern = pattern.replace(/\*\*/g, '.*').replace(/\*/g, '[^/]*').replace(/\./g, '\\.');

    const regex = new RegExp(`^${normalizedPattern}$`);
    return regex.test(filePath);
  });
};

const getAllFiles = (dirPath, arrayOfFiles = []) => {
  const files = readdirSync(dirPath);

  files.forEach((file) => {
    const fullPath = join(dirPath, file);
    const relativePath = relative('.', fullPath);

    if (shouldExcludeFile(relativePath)) {
      return;
    }

    const fileStat = statSync(fullPath);

    if (fileStat.isDirectory()) {
      arrayOfFiles = getAllFiles(fullPath, arrayOfFiles);
    } else {
      arrayOfFiles.push({
        fullPath,
        relativePath: relativePath.replace(/\\/g, '/'),
        size: fileStat.size,
      });
    }
  });

  return arrayOfFiles;
};

const cleanDirectory = (dirPath) => {
  try {
    rmSync(dirPath, { recursive: true, force: true });
    logSuccess(`Cleaned: ${dirPath}`);
  } catch (error) {
    // Directory might not exist, which is fine
  }
};

const ensureDirectoryExists = (dirPath) => {
  try {
    mkdirSync(dirPath, { recursive: true });
  } catch (error) {
    if (error.code !== 'EEXIST') {
      throw error;
    }
  }
};

const copyFileToDestination = async (sourceFile, destinationPath) => {
  const destinationDir = dirname(destinationPath);
  ensureDirectoryExists(destinationDir);

  const sourceStream = createReadStream(sourceFile.fullPath);
  const destinationStream = createWriteStream(destinationPath);

  await pipeline(sourceStream, destinationStream);
};

const copyProjectFiles = async () => {
  logStep('1/4', 'Copying project files...');
  const spinner = createSpinner('Scanning and copying files...');

  const allFiles = getAllFiles('.');
  const tutorBuildDir = join(CONFIG.buildDir, 'tutor');
  let totalSize = 0;

  ensureDirectoryExists(tutorBuildDir);

  for (const file of allFiles) {
    const destinationPath = join(tutorBuildDir, file.relativePath);
    await copyFileToDestination(file, destinationPath);
    totalSize += file.size;
  }

  spinner.stop(
    `Copied ${colors.bright}${allFiles.length}${colors.reset} files (${formatBytes(totalSize)}) to build directory`,
  );
};

const copyFontFiles = async () => {
  logStep('2/4', 'Copying font files...');
  const spinner = createSpinner('Processing font files...');

  try {
    const fontSourceDir = 'v2-library/fonts/tutor-icon';
    const fontDestDir = 'assets/fonts';
    const buildFontDestDir = join(CONFIG.buildDir, 'tutor', fontDestDir);
    let totalSize = 0;

    ensureDirectoryExists(fontDestDir);
    ensureDirectoryExists(buildFontDestDir);

    const fontFiles = readdirSync(fontSourceDir).filter((file) => /\.(woff2|woff|ttf|otf|eot)$/i.test(extname(file)));

    for (const fontFile of fontFiles) {
      const sourcePath = join(fontSourceDir, fontFile);
      const localDestPath = join(fontDestDir, fontFile);
      const buildDestPath = join(buildFontDestDir, fontFile);

      const fileSize = statSync(sourcePath).size;
      totalSize += fileSize;

      await copyFileToDestination({ fullPath: sourcePath }, localDestPath);
      await copyFileToDestination({ fullPath: sourcePath }, buildDestPath);
    }

    spinner.stop(`Copied ${colors.bright}${fontFiles.length}${colors.reset} font files (${formatBytes(totalSize)})`);
  } catch (error) {
    spinner.fail('Font files not found, skipping...');
    logWarning('Font files not found, skipping...');
  }
};

const copyDroipFiles = async () => {
  logStep('3/4', 'Copying Droip files...');
  const spinner = createSpinner('Processing Droip distribution files...');

  try {
    const droipSourceDir = 'includes/droip/dist';
    const droipFiles = getAllFiles(droipSourceDir);
    let totalSize = 0;

    if (droipFiles.length === 0) {
      spinner.fail('No Droip files found, skipping...');
      return;
    }

    for (const file of droipFiles) {
      const relativeToDroip = relative(droipSourceDir, file.fullPath);
      const destinationPath = join(CONFIG.buildDir, 'tutor', 'includes', 'droip', relativeToDroip);
      await copyFileToDestination(file, destinationPath);
      totalSize += file.size;
    }

    spinner.stop(`Copied ${colors.bright}${droipFiles.length}${colors.reset} Droip files (${formatBytes(totalSize)})`);
  } catch (error) {
    // Just use one consistent method to report the error
    spinner.fail(`Droip files not found: ${error.message}`);
  }
};

// Create ZIP archive using archiver
const createValidZipArchive = async () => {
  logStep('4/4', 'Creating ZIP archive...');
  const buildName = `tutor-${versionNumber}.zip`;
  const spinner = createSpinner(`Creating ${buildName}...`);

  try {
    // Remove existing zip if present
    rmSync(buildName, { force: true });

    const allBuildFiles = getAllFiles(CONFIG.buildDir);
    if (allBuildFiles.length === 0) {
      spinner.fail('No files found in build directory');
      throw new Error('No files found in build directory');
    }

    // Setup archiver
    const output = createWriteStream(buildName);
    const archive = archiver('zip', {
      zlib: { level: 9 }, // Maximum compression
    });

    // Setup promise for completion
    const archiveComplete = new Promise((resolve, reject) => {
      output.on('close', resolve);
      archive.on('error', reject);
    });

    // Pipe archive data to output file
    archive.pipe(output);

    let totalSizeBeforeCompression = 0;

    // Add each file to the archive
    for (const file of allBuildFiles) {
      const relativePath = relative(CONFIG.buildDir, file.fullPath);
      const zipPath = relativePath.replace(/\\/g, '/');
      archive.file(file.fullPath, { name: zipPath });
      totalSizeBeforeCompression += file.size;
    }

    // Finalize and wait for completion
    await archive.finalize();
    await archiveComplete;

    // Get compressed file size
    const compressedSize = statSync(buildName).size;
    const compressionRatio = (1 - compressedSize / totalSizeBeforeCompression) * 100;

    spinner.stop(
      `Created archive: ${colors.bright}${buildName}${colors.reset}\n` +
        `  ${symbols.file} Files: ${colors.bright}${allBuildFiles.length}${colors.reset}\n` +
        `  ${symbols.arrow} Original size: ${formatBytes(totalSizeBeforeCompression)}\n` +
        `  ${symbols.arrow} Compressed size: ${formatBytes(compressedSize)}\n` +
        `  ${symbols.arrow} Compression ratio: ${compressionRatio.toFixed(2)}%`,
    );
  } catch (error) {
    spinner.fail(`Failed to create archive: ${error.message}`);
    throw error;
  }
};

const handleBuildError = (error) => {
  logError(`Build failed: ${error.message}`);
  console.log(style.box(`${symbols.error} BUILD FAILED!`, colors.headerError));
  process.exit(1);
};

const executeBuild = async () => {
  const startTime = Date.now();

  try {
    printHeader();
    logInfo('Starting build process...');

    extractVersionNumber();

    if (!versionNumber) {
      throw new Error('Could not extract version number from tutor.php');
    }

    cleanDirectory(CONFIG.buildDir);

    const archiveName = `tutor-${versionNumber}.zip`;
    cleanDirectory(archiveName);

    await copyProjectFiles();
    await copyFontFiles();
    await copyDroipFiles();
    await createValidZipArchive();

    cleanDirectory(CONFIG.buildDir);

    const duration = Date.now() - startTime;
    const durationFormatted = `${(duration / 1000).toFixed(2)}s`;

    printFooter(durationFormatted);
  } catch (error) {
    handleBuildError(error);
  }
};

// Execute if run directly
if (require.main === module) {
  executeBuild();
}

module.exports = { executeBuild };
