import { deleteAsync } from 'del';
import { readFileSync } from 'fs';
import gulp from 'gulp';
import zip from 'gulp-zip';

let versionNumber = '';

try {
  const data = readFileSync('tutor.php', 'utf8');
  versionNumber = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';
  console.log(versionNumber)
} catch (err) {
  console.log(err);
}

const build_name = 'tutor-' + versionNumber + '.zip';

gulp.task('clean-zip', function () {
  return deleteAsync('./' + build_name);
});

gulp.task('clean-build', function () {
  return deleteAsync('./build');
});

gulp.task('copy', function () {
  return gulp
    .src([
      './**/*.*',
      '!./build/**',
      '!./assets/**/*.map',
      '!./assets/react/**',
      '!./assets/scss/**',
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
      '!.husky',
      '!.lintstagedrc'
    ])
    .pipe(gulp.dest('build/tutor/'));
});

gulp.task('copy-fonts', function () {
  return gulp
    .src('assets/fonts')
    .pipe(gulp.dest('v2-library/fonts/fonts/*'));
});

gulp.task("copy-tutor-droip", function () {
  return gulp
    .src("includes/droip/dist/**")
    .pipe(gulp.dest("build/tutor/includes/droip"));
});

gulp.task('make-zip', function () {
  return gulp
    .src('./build/**/*.*')
    .pipe(zip(build_name))
    .pipe(gulp.dest('./'));
});

export const build = gulp.series('clean-zip', 'clean-build', 'copy', 'copy-fonts', 'copy-tutor-droip', 'make-zip', 'clean-build');
