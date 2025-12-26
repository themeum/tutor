/* eslint-disable no-console */
import { deleteAsync } from 'del';
import { existsSync, readFileSync } from 'fs';
import gulp from 'gulp';
import zip from 'gulp-zip';

let versionNumber = '';

try {
  const data = readFileSync('tutor.php', 'utf8');
  versionNumber = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';
  console.log(versionNumber);
} catch (err) {
  console.log(err);
}

const build_name = `tutor-${versionNumber}.zip`;

function cleanZip() {
  return deleteAsync(`./${build_name}`);
}

function cleanBuild() {
  return deleteAsync('./build');
}

function copy() {
  return gulp
    .src(
      [
        './**/*.*',
        '!./build/**',
        '!./assets/**/*.map',
        '!./assets/core/**',
        '!./assets/src/**',
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
        '!./**/*.ignore',
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
        '!.husky',
        '!.lintstagedrc',
        '!./stories/**',
        '!rsbuild.config.ts',
        '!phpstan.neon',
      ],
      {
        allowEmpty: true,
        buffer: true,
        encoding: false,
      },
    )
    .pipe(gulp.dest('build/tutor/'));
}

function copyTutorDroip() {
  const droipDistPath = 'includes/droip/dist';

  if (!existsSync(droipDistPath)) {
    console.log('⚠️ Droip files not found, skipping...');
    return Promise.resolve();
  }

  return gulp
    .src(`${droipDistPath}/**`, {
      allowEmpty: true,
      buffer: true,
      encoding: false,
    })
    .pipe(gulp.dest('build/tutor/includes/droip'));
}

function makeZip() {
  return gulp
    .src('./build/**/*.*', {
      buffer: true,
      allowEmpty: true,
      encoding: false,
    })
    .pipe(zip(build_name))
    .pipe(gulp.dest('./'));
}

export const build = gulp.series(cleanZip, cleanBuild, copy, copyTutorDroip, makeZip, cleanBuild);
