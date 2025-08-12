/* eslint-disable no-console */
import { deleteAsync } from 'del';
import { readFileSync } from 'fs';
import gulp from 'gulp';
import zip from 'gulp-zip';
import { existsSync } from 'node:fs';
import process from 'node:process';

let versionNumber = '';

// Extract version number
const extractVersionNumber = () => {
  try {
    const data = readFileSync('tutor.php', 'utf8');
    versionNumber = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';

    if (!versionNumber) {
      console.error('Version number not found in tutor.php file');
      process.exit(1);
    }

    console.info(`Version detected: ${versionNumber}`);
  } catch (err) {
    console.error(`Error reading version: ${err.message}`);
    process.exit(1);
  }
};

const initializeBuild = (done) => {
  extractVersionNumber();
  done();
};
initializeBuild.displayName = 'initializeBuild';

const buildName = () => `tutor-${versionNumber}.zip`;

const cleanZipFile = () => {
  return deleteAsync(`./${buildName()}`)
    .then(() => {
      console.info(`Cleaned: ${buildName()}`);
    })
    .catch((err) => {
      console.error(`Failed to clean ${buildName()}: ${err.message}`);
      throw err;
    });
};
cleanZipFile.displayName = 'cleanZipFile';

const cleanBuildDirectory = () => {
  return deleteAsync('./build')
    .then(() => {
      console.info('Cleaned: ./build');
    })
    .catch((err) => {
      console.error(`Failed to clean build directory: ${err.message}`);
      throw err;
    });
};
cleanBuildDirectory.displayName = 'cleanBuildDirectory';

const copyProjectFiles = () => {
  return gulp
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
    .pipe(gulp.dest('build/tutor/'));
};
copyProjectFiles.displayName = 'copyProjectFiles';

const copyDroipFiles = () => {
  if (!existsSync('includes/droip/dist')) {
    return Promise.resolve();
  }

  return gulp
    .src('includes/droip/dist/**', {
      buffer: true,
      encoding: false,
      allowEmpty: true,
    })
    .pipe(gulp.dest('build/tutor/includes/droip'));
};
copyDroipFiles.displayName = 'copyDroipFiles';

const createZipFile = () => {
  return gulp
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
    .pipe(gulp.dest('./'));
};
createZipFile.displayName = 'createZipFile';

export const build = gulp.series(
  initializeBuild,
  cleanZipFile,
  cleanBuildDirectory,
  copyProjectFiles,
  copyDroipFiles,
  createZipFile,
);
build.displayName = 'build';
