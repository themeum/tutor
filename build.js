const { createReadStream, createWriteStream, mkdirSync, readFileSync, readdirSync, rmSync, statSync } = require('fs');
const { dirname, extname, join, relative } = require('path');
const { pipeline } = require('stream/promises');
const { createDeflateRaw } = require('zlib');
const { promisify } = require('util');

const deflateRaw = promisify(createDeflateRaw().flush ? createDeflateRaw()._flush : createDeflateRaw);

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

const printHeader = () => {
  console.log(`\n${colors.cyan}${colors.bright}╔════════════════════════════════════════╗${colors.reset}`);
  console.log(
    `${colors.cyan}${colors.bright}║        ${symbols.rocket} TUTOR BUILD PROCESS ${symbols.package}        ║${colors.reset}`,
  );
  console.log(`${colors.cyan}${colors.bright}╚════════════════════════════════════════╝${colors.reset}\n`);
};

const printFooter = (duration) => {
  console.log(`\n${colors.green}${colors.bright}╔════════════════════════════════════════╗${colors.reset}`);
  console.log(
    `${colors.green}${colors.bright}║     ${symbols.success} BUILD COMPLETED SUCCESSFULLY!     ║${colors.reset}`,
  );
  console.log(
    `${colors.green}${colors.bright}║           ${symbols.clock} Duration: ${duration}ms            ║${colors.reset}`,
  );
  console.log(`${colors.green}${colors.bright}╚════════════════════════════════════════╝${colors.reset}\n`);
};

const CONFIG = {
  sourceDir: '.',
  buildDir: './build',
  excludePatterns: [
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

const shouldExcludeFile = (filePath) => {
  return CONFIG.excludePatterns.some((pattern) => {
    const normalizedPattern = pattern.replace(/\*\*/g, '.*').replace(/\*/g, '[^/]*');
    const regex = new RegExp(`^${normalizedPattern.replace(/\//g, '\\/')}`);
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

  ensureDirectoryExists(tutorBuildDir);

  for (const file of allFiles) {
    const destinationPath = join(tutorBuildDir, file.relativePath);
    await copyFileToDestination(file, destinationPath);
  }

  spinner.stop(`Copied ${colors.bright}${allFiles.length}${colors.reset} files to build directory`);
};

const copyFontFiles = async () => {
  logStep('2/4', 'Copying font files...');
  const spinner = createSpinner('Processing font files...');

  try {
    const fontSourceDir = 'v2-library/fonts/tutor-icon';
    const fontDestDir = 'assets/fonts';
    const buildFontDestDir = join(CONFIG.buildDir, 'tutor', fontDestDir);

    ensureDirectoryExists(fontDestDir);
    ensureDirectoryExists(buildFontDestDir);

    const fontFiles = readdirSync(fontSourceDir).filter((file) => /\.(woff2|woff|ttf|otf|eot)$/i.test(extname(file)));

    for (const fontFile of fontFiles) {
      const sourcePath = join(fontSourceDir, fontFile);
      const localDestPath = join(fontDestDir, fontFile);
      const buildDestPath = join(buildFontDestDir, fontFile);

      await copyFileToDestination({ fullPath: sourcePath }, localDestPath);
      await copyFileToDestination({ fullPath: sourcePath }, buildDestPath);
    }

    spinner.stop(`Copied ${colors.bright}${fontFiles.length}${colors.reset} font files`);
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

    for (const file of droipFiles) {
      const relativeToDroip = relative(droipSourceDir, file.fullPath);
      const destinationPath = join(CONFIG.buildDir, 'tutor', 'includes', 'droip', relativeToDroip);
      await copyFileToDestination(file, destinationPath);
    }

    spinner.stop(`Copied ${colors.bright}${droipFiles.length}${colors.reset} Droip files`);
  } catch (error) {
    spinner.fail('Droip files not found, skipping...');
    logWarning('Droip files not found, skipping...');
  }
};

// CRC32 calculation for ZIP files
const calculateCrc32 = (data) => {
  const crcTable = [];
  for (let i = 0; i < 256; i++) {
    let crc = i;
    for (let j = 0; j < 8; j++) {
      crc = crc & 1 ? (crc >>> 1) ^ 0xedb88320 : crc >>> 1;
    }
    crcTable[i] = crc;
  }

  let crc = 0xffffffff;
  for (let i = 0; i < data.length; i++) {
    crc = (crc >>> 8) ^ crcTable[(crc ^ data[i]) & 0xff];
  }
  return (crc ^ 0xffffffff) >>> 0;
};

// Create proper ZIP file format
const createValidZipArchive = async () => {
  logStep('4/4', 'Creating ZIP archive...');
  const buildName = `tutor-${versionNumber}.zip`;
  const spinner = createSpinner(`Creating ${buildName}...`);

  try {
    rmSync(buildName, { force: true });
  } catch (error) {
    // File might not exist
  }

  const allBuildFiles = getAllFiles(CONFIG.buildDir);

  if (allBuildFiles.length === 0) {
    spinner.fail('No files found in build directory');
    throw new Error('No files found in build directory');
  }

  const zipStream = createWriteStream(buildName);
  const centralDirectory = [];
  let currentOffset = 0;

  // Process each file and write to ZIP
  for (const file of allBuildFiles) {
    const relativePath = relative(CONFIG.buildDir, file.fullPath);
    const fileContent = readFileSync(file.fullPath);
    const crc32 = calculateCrc32(fileContent);

    // Convert path separators to forward slashes for ZIP compatibility
    const zipPath = relativePath.replace(/\\/g, '/');
    const pathBuffer = Buffer.from(zipPath, 'utf8');

    // Local file header (30 bytes + filename)
    const localHeader = Buffer.alloc(30);
    localHeader.writeUInt32LE(0x04034b50, 0); // Local file header signature
    localHeader.writeUInt16LE(20, 4); // Version needed to extract
    localHeader.writeUInt16LE(0, 6); // General purpose bit flag
    localHeader.writeUInt16LE(0, 8); // Compression method (0 = stored)
    localHeader.writeUInt16LE(0, 10); // File last modification time
    localHeader.writeUInt16LE(0, 12); // File last modification date
    localHeader.writeUInt32LE(crc32, 14); // CRC-32
    localHeader.writeUInt32LE(fileContent.length, 18); // Compressed size
    localHeader.writeUInt32LE(fileContent.length, 22); // Uncompressed size
    localHeader.writeUInt16LE(pathBuffer.length, 26); // File name length
    localHeader.writeUInt16LE(0, 28); // Extra field length

    // Write local file header
    zipStream.write(localHeader);
    zipStream.write(pathBuffer);
    zipStream.write(fileContent);

    // Store central directory entry info
    centralDirectory.push({
      relativePath: zipPath,
      pathBuffer,
      crc32,
      compressedSize: fileContent.length,
      uncompressedSize: fileContent.length,
      localHeaderOffset: currentOffset,
    });

    currentOffset += 30 + pathBuffer.length + fileContent.length;
  }

  // Write central directory
  const centralDirectoryOffset = currentOffset;
  let centralDirectorySize = 0;

  for (const entry of centralDirectory) {
    // Central directory file header (46 bytes + filename)
    const centralHeader = Buffer.alloc(46);
    centralHeader.writeUInt32LE(0x02014b50, 0); // Central file header signature
    centralHeader.writeUInt16LE(20, 4); // Version made by
    centralHeader.writeUInt16LE(20, 6); // Version needed to extract
    centralHeader.writeUInt16LE(0, 8); // General purpose bit flag
    centralHeader.writeUInt16LE(0, 10); // Compression method
    centralHeader.writeUInt16LE(0, 12); // File last modification time
    centralHeader.writeUInt16LE(0, 14); // File last modification date
    centralHeader.writeUInt32LE(entry.crc32, 16); // CRC-32
    centralHeader.writeUInt32LE(entry.compressedSize, 20); // Compressed size
    centralHeader.writeUInt32LE(entry.uncompressedSize, 24); // Uncompressed size
    centralHeader.writeUInt16LE(entry.pathBuffer.length, 28); // File name length
    centralHeader.writeUInt16LE(0, 30); // Extra field length
    centralHeader.writeUInt16LE(0, 32); // File comment length
    centralHeader.writeUInt16LE(0, 34); // Disk number where file starts
    centralHeader.writeUInt16LE(0, 36); // Internal file attributes
    centralHeader.writeUInt32LE(0, 38); // External file attributes
    centralHeader.writeUInt32LE(entry.localHeaderOffset, 42); // Local header offset

    zipStream.write(centralHeader);
    zipStream.write(entry.pathBuffer);

    centralDirectorySize += 46 + entry.pathBuffer.length;
  }

  // End of central directory record (22 bytes)
  const endRecord = Buffer.alloc(22);
  endRecord.writeUInt32LE(0x06054b50, 0); // End of central directory signature
  endRecord.writeUInt16LE(0, 4); // Number of this disk
  endRecord.writeUInt16LE(0, 6); // Disk where central directory starts
  endRecord.writeUInt16LE(centralDirectory.length, 8); // Number of central directory records on this disk
  endRecord.writeUInt16LE(centralDirectory.length, 10); // Total number of central directory records
  endRecord.writeUInt32LE(centralDirectorySize, 12); // Size of central directory
  endRecord.writeUInt32LE(centralDirectoryOffset, 16); // Offset of central directory
  endRecord.writeUInt16LE(0, 20); // ZIP file comment length

  zipStream.write(endRecord);
  zipStream.end();

  await new Promise((resolve, reject) => {
    zipStream.on('finish', resolve);
    zipStream.on('error', reject);
  });

  spinner.stop(
    `Created archive: ${colors.bright}${buildName}${colors.reset} with ${colors.bright}${allBuildFiles.length}${colors.reset} files`,
  );
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
    printFooter(duration);
  } catch (error) {
    handleBuildError(error);
  }
};

// Execute if run directly
if (require.main === module) {
  executeBuild();
}

module.exports = { executeBuild };
