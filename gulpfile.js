var gulp = require("gulp"),
	sass = require("gulp-sass"),
	rename = require("gulp-rename"),
	prefix = require("gulp-autoprefixer"),
	plumber = require("gulp-plumber"),
	notify = require("gulp-notify"),
	sourcemaps = require("gulp-sourcemaps"),
	wpPot = require('gulp-wp-pot'),
	clean = require("gulp-clean"),
	zip = require("gulp-zip");

const is_dev = process.env.NODE_ENV=='development';

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
	overrideBrowserslist: ["last 2 versions"],
};

var scss_blueprints = {
	tutor_front : {src: "assets/scss/front/main.scss", mode: 'expanded', destination: 'tutor-front.css'},
	tutor_front_min: {src: "assets/scss/front/main.scss", mode: 'compressed', destination: 'tutor-front.min.css'},
	tutor_admin: {src: "assets/scss/admin/main.scss", mode: 'expanded', destination: 'tutor-admin.css'},
	tutor_admin_min: {src: "assets/scss/admin/main.scss", mode: 'compressed', destination: 'tutor-admin.min.css'},
};

var task_keys = Object.keys(scss_blueprints);

for(let task in scss_blueprints) {
	
	let blueprint = scss_blueprints[task];
	
	gulp.task(task, function () {

		var task = gulp.src(blueprint.src).pipe(plumber({errorHandler: onError}));

		is_dev ? task=task.pipe(sourcemaps.init({loadMaps: true})) : 0;

		task = task.pipe(sass({outputStyle: blueprint.mode})).pipe(prefix(prefixerOptions)).pipe(rename(blueprint.destination));

		is_dev ? task=task.pipe(sourcemaps.write(".")) : 0;

		return task.pipe(gulp.dest("assets/css"));
	});
}

gulp.task("watch", function () {
	gulp.watch("assets/scss/**/*.scss", gulp.series(...task_keys));
});

gulp.task('makepot', function () {
	return gulp
		.src('**/*.php')
		.pipe(plumber({
			errorHandler: onError
		}))
		.pipe(wpPot({
			domain: 'tutor',
			package: 'Tutor LMS'
		}))
		.pipe(gulp.dest('languages/tutor.pot'));
});

/**
 * Build
 */
gulp.task("clean-zip", function () {
	return gulp.src("./tutor.zip", {
		read: false,
		allowEmpty: true
	}).pipe(clean());
});

gulp.task("clean-build", function () {
	return gulp.src("./build", {
		read: false,
		allowEmpty: true
	}).pipe(clean());
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

gulp.task("make-zip", function () {
	return gulp.src("./build/**/*.*").pipe(zip("tutor.zip")).pipe(gulp.dest("./"));
});

/**
 * Export tasks
 */
exports.build = gulp.series(
	...task_keys,
	"clean-zip",
	"clean-build",
	//"makepot",
	"copy",
	"make-zip",
	"clean-build"
);
exports.sass = gulp.series(...task_keys);
exports.default = gulp.series(...task_keys, "watch");