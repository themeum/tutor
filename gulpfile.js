var gulp = require("gulp"),
  sass = require("gulp-sass"),
  rename = require("gulp-rename"),
  prefix = require("gulp-autoprefixer"),
  plumber = require("gulp-plumber"),
  notify = require("gulp-notify"),
  sourcemaps = require("gulp-sourcemaps"),
  clean = require("gulp-clean"),
  zip = require("gulp-zip");

var onError = function (err) {
  notify.onError({
    title: "Gulp",
    subtitle: "Failure!",
    message: "Error: <%= error.message %>",
    sound: "Basso",
  })(err);
  this.emit("end");
};

var prefixerOptions = {
  browsers: ["last 2 versions"],
};

gulp.task("styles", function () {
  return gulp
    .src("assets/scss/main.scss")
    .pipe(plumber({ errorHandler: onError }))
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass({ outputStyle: "expanded" }))
    .pipe(prefix(prefixerOptions))
    .pipe(rename("tutor-front.css"))
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest("assets/css"));
});

gulp.task("styles.min", function () {
  return gulp
    .src("assets/scss/main.scss")
    .pipe(plumber({ errorHandler: onError }))
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(sass({ outputStyle: "compressed" }))
    .pipe(prefix(prefixerOptions))
    .pipe(rename("tutor-front.min.css"))
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest("assets/css"));
});

gulp.task("watch", function () {
  gulp.watch("assets/scss/**/*.scss", gulp.series("styles", "styles.min"));
});

/**
 * Build
 */

 gulp.task("clean-zip", function() {
   return gulp.src("./tutor.zip", { read: false, allowEmpty: true }).pipe(clean());
 });

  gulp.task("clean-build", function () {
    return gulp.src("./build", { read: false, allowEmpty: true }).pipe(clean());
  });

 gulp.task("copy", function () {
   return gulp
     .src([
       "./**/*.*",
       "!./build/**",
       "!./assets/**/*.map",
       "!./assets/scss/**",
       "!./assets/.sass-cache",
       "!./node_modules/**",
       "!./**/*.zip",
       "!.github",
       "!./gulpfile.js",
       "!./readme.md",
       "!.DS_Store",
       "!./**/.DS_Store",
       "!./LICENSE.txt",
       "!./package.json",
       "!./package-lock.json",
     ])
     .pipe(gulp.dest("build/tutor/"));
 });

 gulp.task("make-zip", function() {
   return gulp.src("./build/**/*.*").pipe(zip("tutor.zip")).pipe(gulp.dest("./"));
 });

/**
 * Export tasks
 */

exports.build = gulp.series(
  "clean-zip",
  "clean-build",
  "copy",
  "make-zip",
  "clean-build"
);
exports.sass = gulp.series("styles", "styles.min");
exports.default = gulp.series("styles", "styles.min", "watch");
