const { createReadStream, createWriteStream, mkdirSync, readFileSync, readdirSync, rmSync, statSync } = require('fs');
const { dirname, extname, join, relative } = require('path');
const { pipeline } = require('stream/promises');
const archiver = require('archiver');

const ui = {
  colors: {
    reset: '\x1b[0m',
    bright: '\x1b[1m',
    teal: '\x1b[38;5;43m',
    green: '\x1b[38;5;114m',
    red: '\x1b[38;5;203m',
    blue: '\x1b[38;5;75m',
    yellow: '\x1b[38;5;221m',
    orange: '\x1b[38;5;209m',
    white: '\x1b[37m',
    magenta: '\x1b[38;5;177m',
  },
  symbols: {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: '💡',
    rocket: '🚀',
    package: '📦',
    file: '📄',
    arrow: '➡️',
    clock: '⏱️',
    spinner: ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'],
  },
};

const formatBytes = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`;
};

const getVisualWidth = (str) => {
  const cleanStr = str.replace(/\x1b\[[0-9;]*m/g, '');
  const emojiChars = ['✅', '❌', '⚠️', '💡', '🚀', '📦', '📄', '📁', '⏱️', '➡️'];
  let width = 0;

  for (let i = 0; i < cleanStr.length; i++) {
    width += emojiChars.includes(cleanStr[i]) ? 2 : 1;
    if (cleanStr[i] === '️' && i > 0) width--; // Variation selector
  }
  return width;
};

const logger = {
  step: (stepNumber, stepMessage) =>
    console.log(
      `\n${ui.colors.blue}${ui.colors.bright}[${stepNumber}]${ui.colors.reset} ${ui.colors.white}${stepMessage}${ui.colors.reset}`,
    ),
  success: (successMessage) =>
    console.log(`${ui.colors.green}${ui.symbols.success} ${successMessage}${ui.colors.reset}`),
  error: (errorMessage) => console.log(`${ui.colors.red}${ui.symbols.error} ${errorMessage}${ui.colors.reset}`),
  warning: (warningMessage) =>
    console.log(`${ui.colors.orange}${ui.symbols.warning} ${warningMessage}${ui.colors.reset}`),
  info: (infoMessage) => console.log(`${ui.colors.blue}${ui.symbols.info} ${infoMessage}${ui.colors.reset}`),

  box: (boxText, boxColor = ui.colors.teal) => {
    const boxWidth = 50;
    const textLines = boxText.split('\n');
    const boxLines = [`\n${boxColor}${ui.colors.bright}╔${'═'.repeat(boxWidth)}╗${ui.colors.reset}`];

    textLines.forEach((textLine) => {
      const visualWidth = getVisualWidth(textLine);
      const totalPadding = boxWidth - visualWidth;
      const leftPadding = Math.max(0, Math.floor(totalPadding / 2));
      const rightPadding = Math.max(0, totalPadding - leftPadding);
      boxLines.push(
        `${boxColor}${ui.colors.bright}║${' '.repeat(leftPadding)}${textLine}${' '.repeat(rightPadding)}║${ui.colors.reset}`,
      );
    });

    boxLines.push(`${boxColor}${ui.colors.bright}╚${'═'.repeat(boxWidth)}╝${ui.colors.reset}\n`);
    console.log(boxLines.join('\n'));
  },

  spinner: (spinnerMessage) => {
    let spinnerIndex = 0;
    const spinnerInterval = setInterval(() => {
      process.stdout.write(
        `\r${ui.colors.yellow}${ui.symbols.spinner[spinnerIndex]} ${spinnerMessage}${ui.colors.reset}`,
      );
      spinnerIndex = (spinnerIndex + 1) % ui.symbols.spinner.length;
    }, 100);

    return {
      interval: spinnerInterval, // Expose interval for manual clearing
      stop: (completionMessage) => {
        clearInterval(spinnerInterval);
        // Only show message if it's provided and not empty
        if (completionMessage && completionMessage.trim()) {
          process.stdout.write(`\r${ui.colors.green}${ui.symbols.success} ${completionMessage}${ui.colors.reset}\n`);
        } else {
          // Clear the line completely
          process.stdout.write('\r\x1b[K');
        }
      },
      fail: (failureMessage) => {
        clearInterval(spinnerInterval);
        process.stdout.write(
          `\r${ui.colors.red}${ui.symbols.error} ${failureMessage || spinnerMessage}${ui.colors.reset}\n`,
        );
      },
    };
  },
};

// Configuration
const CONFIG = {
  sourceDir: '.',
  buildDir: './build',
  excludePatterns: [
    '*.json',
    './build/**',
    'assets/*.min.css*',
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
    'phpunit.xml*',
    'phpcs.xml*',
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

// File utilities
const fileUtils = {
  ensureDirectoryExists: (directoryPath) => mkdirSync(directoryPath, { recursive: true }),

  cleanPath: (targetPath) => {
    try {
      rmSync(targetPath, { recursive: true, force: true });
      logger.success(`Cleaned: ${targetPath}`);
    } catch (cleanError) {
      if (cleanError.code !== 'ENOENT') {
        logger.error(`Failed to clean ${targetPath}: ${cleanError.message}`);
        throw cleanError;
      }
    }
  },

  copyFile: async (sourceFile, destinationPath) => {
    fileUtils.ensureDirectoryExists(dirname(destinationPath));
    try {
      await pipeline(createReadStream(sourceFile.fullPath), createWriteStream(destinationPath));
    } catch (copyError) {
      logger.error(`Failed to copy ${sourceFile.relativePath}: ${copyError.message}`);
      throw copyError;
    }
  },

  shouldExcludeFile: (filePath) => {
    // Early return: Exclude dot files/folders
    const pathSegments = filePath.split('/');
    if (pathSegments.some((segment) => segment.startsWith('.'))) {
      return true;
    }

    // Check against all exclude patterns
    return CONFIG.excludePatterns.some((pattern) => {
      // Handle directory patterns (ending with /**)
      if (pattern.endsWith('/**')) {
        const basePath = pattern.replace(/\/\*\*$/, '');
        return filePath.startsWith(basePath);
      }

      // Handle wildcard patterns
      if (pattern.includes('*')) {
        const normalizedPattern = pattern.replace(/\*\*/g, '.*').replace(/\*/g, '[^/]*').replace(/\./g, '\\.');
        return new RegExp(`^${normalizedPattern}$`).test(filePath);
      }

      // Direct string match
      return filePath === pattern;
    });
  },

  getAllFilesRecursively: (directoryPath, collectedFiles = []) => {
    try {
      const files = readdirSync(directoryPath);

      files.forEach((fileName) => {
        const fullPath = join(directoryPath, fileName);
        const relativePath = relative('.', fullPath).replace(/\\/g, '/');

        if (fileUtils.shouldExcludeFile(relativePath)) return;

        try {
          const fileStats = statSync(fullPath);
          if (fileStats.isDirectory()) {
            collectedFiles = fileUtils.getAllFilesRecursively(fullPath, collectedFiles);
          } else {
            collectedFiles.push({ fullPath, relativePath, size: fileStats.size });
          }
        } catch (fileStatError) {
          logger.error(`Failed to read file stats for ${relativePath}: ${fileStatError.message}`);
        }
      });

      return collectedFiles;
    } catch (directoryReadError) {
      logger.error(`Failed to read directory ${directoryPath}: ${directoryReadError.message}`);
      throw directoryReadError;
    }
  },
};

// Task executor with spinner - Fixed to handle warning cases properly
const executeTaskWithSpinner = async (taskName, taskFunction, ...functionArguments) => {
  const spinner = logger.spinner(`${taskName}...`);
  try {
    const taskResult = await taskFunction(...functionArguments);
    spinner.stop(taskResult.message);
    return taskResult;
  } catch (taskError) {
    // Check if this is a warning case (directory not found, skipping)
    if (taskError.message.includes('not found, skipping') || taskError.message.includes('files not found')) {
      // Stop spinner without any message for warning cases
      clearInterval(spinner.interval);
      process.stdout.write('\r\x1b[K'); // Clear the current line
      return { message: '' }; // Return empty to avoid any logging
    }

    spinner.fail(`${taskName} failed: ${taskError.message}`);
    logger.error(`Task execution failed for "${taskName}": ${taskError.message}`);
    throw taskError;
  }
};

// Build tasks
const buildTasks = {
  extractVersionNumber: () => {
    try {
      const fileContent = readFileSync('tutor.php', 'utf8');
      versionNumber = fileContent.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';

      if (!versionNumber) {
        const errorMessage = 'Version number not found in tutor.php file';
        logger.error(errorMessage);
        throw new Error(errorMessage);
      }

      return { message: `Version detected: ${ui.colors.bright}${versionNumber}${ui.colors.reset}` };
    } catch (versionExtractionError) {
      if (versionExtractionError.code === 'ENOENT') {
        const errorMessage = 'tutor.php file not found in current directory';
        logger.error(errorMessage);
        throw new Error(errorMessage);
      }
      throw versionExtractionError;
    }
  },

  copyProjectFiles: async () => {
    try {
      const allProjectFiles = fileUtils.getAllFilesRecursively('.');
      if (allProjectFiles.length === 0) {
        const errorMessage = 'No project files found to copy';
        logger.error(errorMessage);
        throw new Error(errorMessage);
      }

      const tutorBuildDirectory = join(CONFIG.buildDir, 'tutor');
      let totalSizeInBytes = 0;

      fileUtils.ensureDirectoryExists(tutorBuildDirectory);

      for (const projectFile of allProjectFiles) {
        await fileUtils.copyFile(projectFile, join(tutorBuildDirectory, projectFile.relativePath));
        totalSizeInBytes += projectFile.size;
      }

      return {
        message: `Copied ${ui.colors.bright}${allProjectFiles.length}${ui.colors.reset} files (${formatBytes(totalSizeInBytes)}) to build directory`,
        files: allProjectFiles.length,
        size: totalSizeInBytes,
      };
    } catch (copyError) {
      logger.error(`Failed to copy project files: ${copyError.message}`);
      throw copyError;
    }
  },

  copySpecialFiles: async (sourceDirectory, destinationDirectory, fileDescription) => {
    try {
      const specialFiles = fileUtils.getAllFilesRecursively(sourceDirectory);

      // Early return: Handle empty directory case
      if (specialFiles.length === 0) {
        const warningMessage = `${fileDescription} files not found in ${sourceDirectory}, skipping...`;
        logger.warning(warningMessage);
        throw new Error(warningMessage); // Throw to prevent success message
      }

      let totalSizeInBytes = 0;
      for (const specialFile of specialFiles) {
        const relativePath = relative(sourceDirectory, specialFile.fullPath);
        const destinationPath = join(CONFIG.buildDir, 'tutor', destinationDirectory, relativePath);
        await fileUtils.copyFile(specialFile, destinationPath);
        totalSizeInBytes += specialFile.size;
      }

      return {
        message: `Copied ${ui.colors.bright}${specialFiles.length}${ui.colors.reset} ${fileDescription} files (${formatBytes(totalSizeInBytes)})`,
        files: specialFiles.length,
        size: totalSizeInBytes,
      };
    } catch (specialFilesError) {
      // Early return: Handle directory not found case
      if (specialFilesError.code === 'ENOENT') {
        const warningMessage = `${fileDescription} directory not found, skipping...`;
        logger.warning(warningMessage);
        throw new Error(warningMessage); // Throw to prevent success message
      }

      // For other errors, log and throw
      const errorMessage = `Failed to process ${fileDescription} files: ${specialFilesError.message}`;
      logger.error(errorMessage);
      throw specialFilesError;
    }
  },

  createArchive: async () => {
    const archiveName = `tutor-${versionNumber}.zip`;
    fileUtils.cleanPath(archiveName);

    try {
      const allBuildFiles = fileUtils.getAllFilesRecursively(CONFIG.buildDir);
      if (allBuildFiles.length === 0) {
        const errorMessage = 'No files found in build directory for archiving';
        logger.error(errorMessage);
        throw new Error(errorMessage);
      }

      const outputStream = createWriteStream(archiveName);
      const archiveInstance = archiver('zip', { zlib: { level: 9 } });

      // Create Promise for archive completion
      const handleArchiveCompletion = new Promise((resolve, reject) => {
        outputStream.on('close', resolve);
        outputStream.on('error', reject);
        archiveInstance.on('error', reject);
      });

      archiveInstance.pipe(outputStream);

      let totalSizeBeforeCompression = 0;
      for (const buildFile of allBuildFiles) {
        const relativePath = relative(CONFIG.buildDir, buildFile.fullPath);
        archiveInstance.file(buildFile.fullPath, { name: relativePath.replace(/\\/g, '/') });
        totalSizeBeforeCompression += buildFile.size;
      }

      await archiveInstance.finalize();
      await handleArchiveCompletion;

      const compressedSize = statSync(archiveName).size;
      const compressionRatio = (1 - compressedSize / totalSizeBeforeCompression) * 100;

      return {
        message:
          `Created archive: ${ui.colors.bright}${archiveName}${ui.colors.reset}\n` +
          `  ${ui.symbols.file} Files: ${ui.colors.bright}${allBuildFiles.length}${ui.colors.reset}\n` +
          `  ${ui.symbols.arrow} Original size: ${formatBytes(totalSizeBeforeCompression)}\n` +
          `  ${ui.symbols.arrow} Compressed size: ${formatBytes(compressedSize)}\n` +
          `  ${ui.symbols.arrow} Compression ratio: ${compressionRatio.toFixed(2)}%`,
      };
    } catch (archiveError) {
      logger.error(`Failed to create archive: ${archiveError.message}`);
      throw archiveError;
    }
  },
};

// Main execution
const executeBuildProcess = async () => {
  const buildStartTime = Date.now();

  try {
    // Header
    const asciiArt = `${ui.colors.magenta}${ui.colors.bright}
███████████████                                                                                    
  █████████████         ███                 ███                                                     
   ███████████          ███                 ███                                                     
 ██████████████         ███                 ███                  ██                           ███   
████ ███████ ███      ███████████     ███ ███████  ████████  ██████     ██     ███      ███ ███████ 
██ ███ ███ ██ ███     ███████████     ███ ██████████████████ ██████     ██     █████  █████ ██    █ 
██ ███ ██████  ██       ███  ████     ███   ███  ███     ███████        ██     ██ ██████ ██ █████   
███ ██ ███ ██ ███       ███   ███     ███   ███  ███     ███████        ██     ██  ████  ██    █████
████   ██   █████       ███   █████ █████   ███  █████ █████ ███        ██     ██   ██   ████     ██
  ██████ ████████       ███    ██████████   ███   █████████  ███        █████████        ██ ████████
   █████████████                                                                              ██   
         ████████${ui.colors.reset}`;

    console.log(asciiArt);
    logger.box(`${ui.symbols.rocket} BUILD PROCESS ${ui.symbols.package}`, ui.colors.magenta);
    logger.info('Starting build process...');

    // Extract version
    await executeTaskWithSpinner('Extracting version number from tutor.php', buildTasks.extractVersionNumber);

    // Clean directories
    fileUtils.cleanPath(CONFIG.buildDir);
    fileUtils.cleanPath(`tutor-${versionNumber}.zip`);

    // Execute build steps
    logger.step('1/4', 'Copying project files...');
    await executeTaskWithSpinner('Scanning and copying files', buildTasks.copyProjectFiles);

    logger.step('2/4', 'Copying font files...');
    await executeTaskWithSpinner(
      'Processing font files',
      buildTasks.copySpecialFiles,
      'v2-library/fonts/tutor-icon',
      'assets/fonts',
      'font',
    );

    logger.step('3/4', 'Copying Droip files...');
    await executeTaskWithSpinner(
      'Processing Droip distribution files',
      buildTasks.copySpecialFiles,
      'includes/droip/dist',
      'includes/droip',
      'Droip',
    );

    logger.step('4/4', 'Creating ZIP archive...');
    await executeTaskWithSpinner(`Creating tutor-${versionNumber}.zip`, buildTasks.createArchive);

    // Cleanup and finish
    fileUtils.cleanPath(CONFIG.buildDir);

    const buildDuration = `${((Date.now() - buildStartTime) / 1000).toFixed(2)}s`;
    logger.box(`✅ BUILD COMPLETED SUCCESSFULLY!\n⏱️ Duration: ${buildDuration}`, ui.colors.green);
  } catch (buildError) {
    logger.error(`Build process failed: ${buildError.message}`);
    logger.box(`${ui.symbols.error} BUILD FAILED!`, ui.colors.red);
    process.exit(1);
  }
};

// Execute if run directly
if (require.main === module) {
  executeBuildProcess();
}

module.exports = { executeBuild: executeBuildProcess };
