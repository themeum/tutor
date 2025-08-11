/* eslint-disable no-console */
import { deleteAsync } from 'del';
import { readFileSync } from 'fs';
import gulp from 'gulp';
import zip from 'gulp-zip';
import { existsSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';
import process from 'node:process';

// UI Configuration
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
    brand: '\x1b[38;2;0;73;248m',
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

const getVisualWidth = (str) => {
  // eslint-disable-next-line no-control-regex
  const cleanStr = str.replace(/\x1b\[[0-9;]*m/g, '');
  const emojiChars = ['✅', '❌', '⚠️', '💡', '🚀', '📦', '📄', '📁', '⏱️', '➡️'];
  let width = 0;

  for (let i = 0; i < cleanStr.length; i++) {
    width += emojiChars.includes(cleanStr[i]) ? 2 : 1;
    if (cleanStr[i] === '️' && i > 0) width--; // Variation selector
  }
  return width;
};

const formatBytes = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`;
};

const getFilesInfo = (dir) => {
  let count = 0;
  let totalSize = 0;

  const items = readdirSync(dir);
  for (const item of items) {
    const fullPath = join(dir, item);
    const stat = statSync(fullPath);
    if (stat.isDirectory()) {
      const { count: subDirCount, totalSize: subDirSize } = getFilesInfo(fullPath);
      count += subDirCount;
      totalSize += subDirSize;
    } else if (stat.isFile()) {
      count++;
      totalSize += stat.size;
    }
  }

  return { count, totalSize };
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
      interval: spinnerInterval,
      stop: (completionMessage) => {
        clearInterval(spinnerInterval);
        if (completionMessage && completionMessage.trim()) {
          process.stdout.write(`\r${ui.colors.green}${ui.symbols.success} ${completionMessage}${ui.colors.reset}\n`);
        } else {
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

// Global variables
let versionNumber = '';
let buildStartTime;

// Extract version number
const extractVersionNumber = () => {
  const spinner = logger.spinner('Extracting version number from tutor.php');

  try {
    const data = readFileSync('tutor.php', 'utf8');
    versionNumber = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';

    if (!versionNumber) {
      spinner.fail('Version number not found in tutor.php file');
      process.exit(1);
    }

    spinner.stop(`Version detected: ${ui.colors.bright}${versionNumber}${ui.colors.reset}`);
  } catch (err) {
    spinner.fail(`Error reading version: ${err.message}`);
    process.exit(1);
  }
};

// Show ASCII art header
const showHeader = () => {
  const asciiArt = `${ui.colors.brand}${ui.colors.bright}
███████████████                                                                                      
  █████████████         ███                  ███                                                      
    ██████████          ███                  ███                                                      
  ██████████████        ███                  ███                                                      
███   █████   ███     ███████ ███      ███ ███████  ████████   ███ ██     ██     ███      ███  ██████ 
██  ██ ███ ██  ██       ███   ███      ███   ███   ███    ███  █████      ██     ████    ████ ██    ██
██  ██ ███ ██  ██       ███   ███      ███   ███  ███      ███ ███        ██     ██ ███ ██ ██ ████    
██  ██ ███ ██  ██       ███   ███      ███   ███  ███      ███ ███        ██     ██  ████  ██    █████
████   ███   ████       ███    ███    ████   ███   ███    ███  ███        ██     ██   ██   ██ █      █
  ████     ██████       ███     ██████████   ███    ████████   ███        █████████        ██ ████████
    █████████████                                                                                     
       ██████████${ui.colors.reset}`;

  console.log(asciiArt);
  logger.box(`${ui.symbols.rocket} BUILD PROCESS ${ui.symbols.package}`, ui.colors.brand);
  logger.info('Starting build process...');
  buildStartTime = Date.now();
};

// Initialize build process
const initializeBuild = (done) => {
  showHeader();
  extractVersionNumber();
  done();
};
initializeBuild.displayName = 'initializeBuild';

const buildName = () => `tutor-${versionNumber}.zip`;

// Build-related tasks with descriptive names and logging
const cleanZipFile = () => {
  const spinner = logger.spinner(`Cleaning previous ${buildName()}`);

  return deleteAsync(`./${buildName()}`)
    .then(() => {
      spinner.stop(`Cleaned: ${buildName()}`);
    })
    .catch((err) => {
      spinner.fail(`Failed to clean ${buildName()}: ${err.message}`);
      throw err;
    });
};
cleanZipFile.displayName = 'cleanZipFile';

const cleanBuildDirectory = () => {
  const spinner = logger.spinner('Cleaning build directory');

  return deleteAsync('./build')
    .then(() => {
      spinner.stop('Cleaned: ./build');
    })
    .catch((err) => {
      spinner.fail(`Failed to clean build directory: ${err.message}`);
      throw err;
    });
};
cleanBuildDirectory.displayName = 'cleanBuildDirectory';

const copyProjectFiles = () => {
  logger.step('1/3', 'Copying project files...');
  const spinner = logger.spinner('Scanning and copying project files');

  return new Promise((resolve, reject) => {
    gulp
      .src(
        [
          './**/*.*',
          '!./build/**',
          '!./assets/**/*.map',
          '!./assets/react/**',
          '!./assets/scss/**',
          '!./assets/css/fonts/**',
          '!./assets/css/images/**',
          '!./assets/.sass-cache',
          '!./node_modules/**',
          '!./v2-library/**',
          '!./test/**',
          '!./.docz/**',
          '!./**/*.zip',
          '!.github',
          '!.vscode',
          '!./readme.md',
          '!.DS_Store',
          '!./**/.DS_Store',
          '!./LICENSE.txt',
          '!./*.lock',
          '!./*.js',
          '!./*.mjs',
          '!./*.json',
          '!yarn-error.log',
          '!bin/**',
          '!tests/**',
          '!.env',
          '!vendor/bin/**',
          '!vendor/doctrine/**',
          '!vendor/myclabs/**',
          '!vendor/nikic/**',
          '!vendor/phar-io/**',
          '!vendor/phpdocumentor/**',
          '!vendor/phpspec/**',
          '!vendor/phpunit/**',
          '!vendor/sebastian/**',
          '!vendor/theseer/**',
          '!vendor/webmozart/**',
          '!vendor/yoast/**',
          '!.phpunit.result.cache',
          '!*.yml',
          '!*.yaml',
          '!phpunit.xml.dist',
          '!phpunit.xml',
          '!phpcs.xml',
          '!phpcs.xml.dist',
          '!./tutor-droip/**',
          '!./includes/droip/**',
          '!./cypress/**',
          '!./cypress.config.ts',
          '!.husky/**',
          '!.lintstagedrc',
          '!./assets/*.min.css',
        ],
        {
          buffer: true,
          encoding: false,
        },
      )
      .pipe(gulp.dest('build/tutor/'))
      .on('end', () => {
        const fileInfo = getFilesInfo('build/tutor/');
        spinner.stop(
          `Copied ${ui.colors.bright}${fileInfo.count}${ui.colors.reset} files (${formatBytes(fileInfo.totalSize)}) to build directory`,
        );
        resolve();
      })
      .on('error', (err) => {
        spinner.fail(`Failed to copy project files: ${err.message}`);
        reject(err);
      });
  });
};
copyProjectFiles.displayName = 'copyProjectFiles';

const copyDroipFiles = () => {
  logger.step('2/3', 'Copying Droip files...');
  const spinner = logger.spinner('Processing Droip distribution files');

  if (!existsSync('includes/droip/dist')) {
    spinner.stop('');
    logger.warning('Droip files not found, skipping...');
    return Promise.resolve();
  }

  return new Promise((resolve) => {
    gulp
      .src('includes/droip/dist/**', {
        buffer: true,
        encoding: false,
        allowEmpty: true,
      })
      .pipe(gulp.dest('build/tutor/includes/droip'))
      .on('end', () => {
        spinner.stop('Droip files copied successfully');
        resolve();
      })
      .on('error', () => {
        spinner.stop('');
        logger.warning('Droip files not found, skipping...');
        resolve();
      });
  });
};
copyDroipFiles.displayName = 'copyDroipFiles';

const createZipFile = () => {
  logger.step('3/3', 'Creating ZIP archive...');
  const spinner = logger.spinner(`Creating ${buildName()}`);

  return new Promise((resolve, reject) => {
    gulp
      .src('./build/**/*.*', {
        buffer: true,
        encoding: false,
        base: './build',
      })
      .pipe(
        zip(buildName(), {
          compress: true,
        }),
      )
      .pipe(gulp.dest('./'))
      .on('end', () => {
        const zippedSize = statSync(buildName()).size;
        const totalSize = getFilesInfo('build/tutor/').totalSize;
        const compressionRatio = (1 - zippedSize / totalSize) * 100;
        const message =
          `Created archive: ${ui.colors.bright}${buildName()}${ui.colors.reset}\n` +
          `  ${ui.symbols.file} Files: ${ui.colors.bright}${getFilesInfo('build/tutor/').count}${ui.colors.reset}\n` +
          `  ${ui.symbols.arrow}  Original size: ${formatBytes(totalSize)}\n` +
          `  ${ui.symbols.arrow}  Compressed size: ${formatBytes(zippedSize)}\n` +
          `  ${ui.symbols.arrow}  Compression ratio: ${compressionRatio.toFixed(2)}%`;
        spinner.stop(message);
        resolve();
      })
      .on('error', (err) => {
        spinner.fail(`Failed to create ZIP archive: ${err.message}`);
        reject(err);
      });
  });
};
createZipFile.displayName = 'createZipFile';

const finalizeBuild = (done) => {
  const buildDuration = `${((Date.now() - buildStartTime) / 1000).toFixed(2)}s`;
  logger.box(`✅ BUILD COMPLETED SUCCESSFULLY!\n⏱️ Duration: ${buildDuration}`, ui.colors.green);
  done();
};
finalizeBuild.displayName = 'finalizeBuild';

const build = gulp.series(
  initializeBuild,
  cleanZipFile,
  cleanBuildDirectory,
  copyProjectFiles,
  copyDroipFiles,
  createZipFile,
  finalizeBuild,
);
build.displayName = 'build';

export { build };
