const { createReadStream, createWriteStream, mkdirSync, readFileSync, readdirSync, rmSync, statSync } = require('fs');
const { dirname, extname, join, relative } = require('path');
const { pipeline } = require('stream/promises');
const archiver = require('archiver');

// Console styling utilities
const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  dim: '\x1b[2m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  magenta: '\x1b[35m',
  cyan: '\x1b[36m',
  white: '\x1b[37m',
  bgRed: '\x1b[41m',
  bgGreen: '\x1b[42m',
  bgYellow: '\x1b[43m',
  bgBlue: '\x1b[44m',
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
  checkmark: '✓',
  cross: '✗',
  arrow: '→',
  spinner: ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
};

const logSuccess = (message) => {
  console.log(`${colors.green}${symbols.success} ${message}${colors.reset}`);
};

const logError = (message) => {
  console.log(`${colors.red}${symbols.error} ${message}${colors.reset}`);
};

const logWarning = (message) => {
  console.log(`${colors.yellow}${symbols.warning} ${message}${colors.reset}`);
};

const logInfo = (message) => {
  console.log(`${colors.cyan}${symbols.info} ${message}${colors.reset}`);
};

const logStep = (step, message) => {
  console.log(`${colors.blue}${colors.bright}[${step}]${colors.reset} ${colors.white}${message}${colors.reset}`);
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
      process.stdout.write(`\r${colors.green}${symbols.checkmark} ${successMessage || message}${colors.reset}\n`);
    },
    fail: (errorMessage) => {
      clearInterval(interval);
      process.stdout.write(`\r${colors.red}${symbols.cross} ${errorMessage || message}${colors.reset}\n`);
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
  console.log(`\n${colors.cyan}${colors.bright}╔═════════════════════════════════════════╗${colors.reset}`);
  console.log(
    `${colors.cyan}${colors.bright}║        ${symbols.rocket} TUTOR BUILD PROCESS ${symbols.package}        ║${colors.reset}`,
  );
  console.log(`${colors.cyan}${colors.bright}╚═════════════════════════════════════════╝${colors.reset}\n`);
};

const printFooter = (duration) => {
  console.log(`\n${colors.green}${colors.bright}╔══════════════════════════════════════════╗${colors.reset}`);
  console.log(
    `${colors.green}${colors.bright}║     ${symbols.success} BUILD COMPLETED SUCCESSFULLY!     ║${colors.reset}`,
  );
  console.log(
    `${colors.green}${colors.bright}║           ${symbols.clock} Duration: ${duration}ms            ║${colors.reset}`,
  );
  console.log(`${colors.green}${colors.bright}╚══════════════════════════════════════════╝${colors.reset}\n`);
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

    spinner.stop(`Version extracted: ${colors.bright}${versionNumber}${colors.reset}`);
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

  // Critical directories to always exclude
  const criticalExclusions = ['assets/react', 'assets/scss', 'node_modules', 'v2-library', 'cypress', 'tests'];

  // Early return if path contains any critical exclusion
  if (criticalExclusions.some((excluded) => filePath.includes(excluded))) {
    return true;
  }

  // Continue with existing pattern matching
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

    for (const file of droipFiles) {
      const relativeToDroip = relative(droipSourceDir, file.fullPath);
      const destinationPath = join(CONFIG.buildDir, 'tutor', 'includes', 'droip', relativeToDroip);
      await copyFileToDestination(file, destinationPath);
      totalSize += file.size;
    }

    spinner.stop(`Copied ${colors.bright}${droipFiles.length}${colors.reset} Droip files (${formatBytes(totalSize)})`);
  } catch (error) {
    spinner.fail('Droip files not found, skipping...');
    logWarning('Droip files not found, skipping...');
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
  console.log(`\n${colors.red}${colors.bright}╔════════════════════════════════════════╗${colors.reset}`);
  console.log(`${colors.red}${colors.bright}║          ${symbols.error} BUILD FAILED!              ║${colors.reset}`);
  console.log(`${colors.red}${colors.bright}╚════════════════════════════════════════╝${colors.reset}\n`);
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
    const durationFormatted = duration > 1000 ? `${(duration / 1000).toFixed(2)}s` : `${duration}ms`;

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
