import { deleteAsync } from 'del';
import { readFileSync } from 'fs';
import gulp from 'gulp';
import notify from 'gulp-notify';
import plumber from 'gulp-plumber';
import rename from 'gulp-rename';
import replace from 'gulp-replace';
import sourcemaps from 'gulp-sourcemaps';
import gulpWatch from 'gulp-watch';
import zip from 'gulp-zip';
import * as sass from 'sass';

// Dynamic import for gulp-sass since it requires ES module syntax
const { default: gulpSass } = await import('gulp-sass');
const sassCompiler = gulpSass(sass);

let versionNumber = '';

try {
  const data = readFileSync('tutor.php', 'utf8');
  versionNumber = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';
  console.log(versionNumber);
} catch (err) {
  console.error('Error reading version:', err);
}

const buildName = `tutor-${versionNumber}.zip`;

const handleError = (errorEvent) => {
  notify.onError({
    title: 'Gulp',
    subtitle: 'Failure!',
    message: 'Error: <%= error.message %>',
    sound: 'Basso',
  })(errorEvent);
  this.emit('end');
};

const scssBlueprints = {
  tutor_front: {
    src: 'assets/scss/front/index.scss',
    mode: 'expanded',
    destination: 'tutor-front.min.css',
  },
  tutor_admin: {
    src: 'assets/scss/admin-dashboard/index.scss',
    mode: 'expanded',
    destination: 'tutor-admin.min.css',
  },
  tutor_setup: {
    src: 'assets/scss/admin-dashboard/tutor-setup.scss',
    mode: 'expanded',
    destination: 'tutor-setup.min.css',
  },
  tutor_v2: {
    src: 'v2-library/src/scss/main.scss',
    mode: 'expanded',
    destination: 'tutor.min.css',
  },
  tutor_v2_rtl: {
    src: 'v2-library/src/scss/main.rtl.scss',
    mode: 'expanded',
    destination: 'tutor.rtl.min.css',
  },
  tutor_icon: {
    src: 'v2-library/fonts/tutor-icon/tutor-icon.css',
    mode: 'expanded',
    destination: 'tutor-icon.min.css',
    dest_path: 'assets/css',
  },
  tutor_front_dashboard: {
    src: 'assets/scss/frontend-dashboard/index.scss',
    mode: 'expanded',
    destination: 'tutor-frontend-dashboard.min.css',
  },
  tutor_import: {
    src: 'assets/scss/admin-dashboard/template-import.scss',
    mode: 'expanded',
    destination: 'tutor-template-import.min.css',
  },
};

const createSassTask = (taskName, blueprintConfig) => {
  const sassTaskFunction = () => {
    let compilationStream = gulp
      .src(blueprintConfig.src)
      .pipe(plumber({ errorHandler: handleError }))
      .pipe(sourcemaps.init({ loadMaps: true, largeFile: true }))
      .pipe(
        sassCompiler({
          outputStyle: 'compressed',
          sass: sass,
          silenceDeprecations: [
            'abs-percent',
            'color-functions',
            'global-builtin',
            'import',
            'legacy-js-api',
            'mixed-decls',
          ],
        }),
      );

    // Cache bust font URLs for icon task and fix font paths
    if (taskName === 'compileTutorIcon') {
      compilationStream = compilationStream.pipe(
        replace(
          /(url\(['"]?)(?:\.\.\/)?fonts\/tutor-icon\//g,
          `$1../fonts/` // FIX: Remove tutor-icon subfolder, fonts are directly in assets/fonts
        )
      ).pipe(
        replace(
          /(url\(['"]?[^)'"]+\.(woff2?|woff|ttf|otf|eot))(['"]?\))/g,
          `$1?v=${versionNumber}$3`
        )
      );
    }

    return compilationStream
      .pipe(rename(blueprintConfig.destination))
      .pipe(gulp.dest(blueprintConfig.dest_path || 'assets/css'));
  };

  sassTaskFunction.displayName = taskName;
  return sassTaskFunction;
};

// Generate SCSS compilation tasks with proper names
const compileTutorFront = createSassTask('compileTutorFront', scssBlueprints.tutor_front);
const compileTutorAdmin = createSassTask('compileTutorAdmin', scssBlueprints.tutor_admin);
const compileTutorSetup = createSassTask('compileTutorSetup', scssBlueprints.tutor_setup);
const compileTutorV2 = createSassTask('compileTutorV2', scssBlueprints.tutor_v2);
const compileTutorV2Rtl = createSassTask('compileTutorV2Rtl', scssBlueprints.tutor_v2_rtl);
const compileTutorIcon = createSassTask('compileTutorIcon', scssBlueprints.tutor_icon);
const compileTutorFrontDashboard = createSassTask('compileTutorFrontDashboard', scssBlueprints.tutor_front_dashboard);
const compileTutorImport = createSassTask('compileTutorImport', scssBlueprints.tutor_import);

const handleScssFileChange = (changeEvent) => {
  const changedFilePath = changeEvent.history[0];

  if (changedFilePath.includes('/front/')) {
    return gulp.parallel(compileTutorFront)();
  }

  if (changedFilePath.includes('/admin-dashboard/')) {
    return gulp.parallel(compileTutorAdmin, compileTutorSetup, compileTutorImport)();
  }

  if (changedFilePath.includes('/frontend-dashboard/')) {
    return gulp.parallel(compileTutorFrontDashboard)();
  }

  if (changedFilePath.includes('modules/')) {
    return gulp.parallel(compileTutorFront, compileTutorAdmin, compileTutorFrontDashboard)();
  }

  // Default: compile all tasks
  return gulp.parallel(
    compileTutorFront,
    compileTutorAdmin,
    compileTutorSetup,
    compileTutorV2,
    compileTutorV2Rtl,
    compileTutorIcon,
    compileTutorFrontDashboard,
    compileTutorImport,
  )();
};

const watchScssFiles = () => {
  return gulpWatch('./**/*.scss', handleScssFileChange);
};
watchScssFiles.displayName = 'watchScssFiles';

// Build-related tasks with descriptive names
const cleanZipFile = () => {
  return deleteAsync(`./${buildName}`);
};
cleanZipFile.displayName = 'cleanZipFile';

const cleanBuildDirectory = () => {
  return deleteAsync('./build');
};
cleanBuildDirectory.displayName = 'cleanBuildDirectory';

const copyProjectFiles = () => {
  return gulp
    .src([
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
    ], {
      buffer: true,
      encoding: false
    })
    .pipe(gulp.dest('build/tutor/'));
};
copyProjectFiles.displayName = 'copyProjectFiles';

const copyFontFiles = () => {
  return gulp.src('v2-library/fonts/tutor-icon/*.{woff2,woff,ttf,otf,eot}', {
    buffer: true,
    encoding: false
  }).pipe(gulp.dest('assets/fonts'));
};
copyFontFiles.displayName = 'copyFontFiles';

const copyTutorDroipFiles = () => {
  return gulp.src('includes/droip/dist/**', {
    buffer: true,
    encoding: false
  }).pipe(gulp.dest('build/tutor/includes/droip'));
};
copyTutorDroipFiles.displayName = 'copyTutorDroipFiles';

const copyTutorIconFonts = () => {
  return gulp.src('v2-library/fonts/tutor-icon/*.{woff2,woff,ttf,otf,eot}', {
    buffer: true,
    encoding: false
  }).pipe(gulp.dest('build/tutor/assets/fonts/'));
};
copyTutorIconFonts.displayName = 'copyTutorIconFonts';

const createZipFile = () => {
  return gulp.src('./build/**/*.*', {
    buffer: true,
    encoding: false,
    base: './build'
  })
    .pipe(zip(buildName, {
      compress: true,
      level: 6
    }))
    .pipe(gulp.dest('./'));
};
createZipFile.displayName = 'createZipFile';

const compileAllScssFiles = gulp.parallel(
  compileTutorFront,
  compileTutorAdmin,
  compileTutorSetup,
  compileTutorV2,
  compileTutorV2Rtl,
  compileTutorIcon,
  compileTutorFrontDashboard,
  compileTutorImport,
);
compileAllScssFiles.displayName = 'compileAllScssFiles';

const buildProject = gulp.series(
  compileAllScssFiles,
  cleanZipFile,
  cleanBuildDirectory,
  copyProjectFiles,
  copyFontFiles,
  copyTutorIconFonts,
  copyTutorDroipFiles,
  createZipFile,
  cleanBuildDirectory,
);
buildProject.displayName = 'buildProject';

const developmentWorkflow = gulp.parallel(compileAllScssFiles, watchScssFiles);
developmentWorkflow.displayName = 'developmentWorkflow';

gulp.task('default', developmentWorkflow);
gulp.task('build', buildProject);