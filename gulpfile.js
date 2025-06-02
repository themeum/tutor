var gulp = require('gulp'),
	sass = require('gulp-sass')(require('sass')),
	sourcemaps = require('gulp-sourcemaps'),
	rename = require('gulp-rename'),
	plumber = require('gulp-plumber'),
	notify = require('gulp-notify'),
	clean = require('gulp-clean'),
	zip = require('gulp-zip'),
	watch = require("gulp-watch"),
	replace = require("gulp-replace"),
	fs = require('fs'),
	path = require('path'),
	versionNumber = '';

try {
	const data = fs.readFileSync('tutor.php', 'utf8');
	versionNumber = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';
	console.log(versionNumber)
} catch (err) { }

const build_name = 'tutor-' + versionNumber + '.zip';

var onError = function (err) {
	notify.onError({
		title: 'Gulp',
		subtitle: 'Failure!',
		message: 'Error: <%= error.message %>',
		sound: 'Basso',
	})(err);
	this.emit('end');
};

var scss_blueprints = {
	tutor_front: { src: 'assets/scss/front/index.scss', mode: 'expanded', destination: 'tutor-front.min.css' },

	tutor_admin: { src: 'assets/scss/admin-dashboard/index.scss', mode: 'expanded', destination: 'tutor-admin.min.css' },

	tutor_setup: {
		src: 'assets/scss/admin-dashboard/tutor-setup.scss',
		mode: 'expanded',
		destination: 'tutor-setup.min.css',
	},

	tutor_v2: { src: 'v2-library/src/scss/main.scss', mode: 'expanded', destination: 'tutor.min.css' },
	tutor_v2_rtl: { src: 'v2-library/src/scss/main.rtl.scss', mode: 'expanded', destination: 'tutor.rtl.min.css' },

	tutor_icon: {
		src: 'v2-library/fonts/tutor-icon/tutor-icon.css',
		mode: 'expanded',
		destination: 'tutor-icon.min.css',
	},

	tutor_front_dashboard: {
		src: 'assets/scss/frontend-dashboard/index.scss',
		mode: 'expanded',
		destination: 'tutor-frontend-dashboard.min.css',
	},

	tutor_import: {
		src: 'assets/scss/admin-dashboard/template-import.scss',
		mode: 'expanded',
		destination: 'tutor-template-import.min.css'
	},

};

var task_keys = Object.keys(scss_blueprints);

for (let task in scss_blueprints) {
	let blueprint = scss_blueprints[task];

	gulp.task(task, function () {
		let stream = gulp
			.src(blueprint.src)
			.pipe(plumber({ errorHandler: onError }))
			.pipe(sourcemaps.init({ loadMaps: true, largeFile: true }))
			.pipe(sass({
				outputStyle: 'compressed',
				sass: require('sass'),
				silenceDeprecations: [
					"abs-percent",
					"color-functions",
					"global-builtin",
					"import",
					"legacy-js-api",
					"mixed-decls"
				]
			}));

		// Cache bust font URLs like .woff, .woff2, .ttf, etc.
		if (task === 'tutor_icon') {
			stream = stream.pipe(
				replace(
					/(url\(['"]?[^)'"]+\.(woff2?|woff|ttf|otf))(['"]?\))/g,
					`$1?v=${versionNumber}$3`
				)
			);
		}

		return stream
			.pipe(rename(blueprint.destination))
			.pipe(gulp.dest(blueprint.dest_path || 'assets/css'));
	});
}

gulp.task('watch', function () {
	return watch('./**/*.scss', function (e) {
		if (e.history[0].includes('/front/')) {
			gulp.parallel('tutor_front')();
		} else if (e.history[0].includes('/admin-dashboard/')) {
			gulp.parallel('tutor_admin', 'tutor_setup', 'tutor_import')();
		} else if (e.history[0].includes('/frontend-dashboard/')) {
			gulp.parallel('tutor_front_dashboard')();
		} else if (e.history[0].includes('modules/')) {
			gulp.parallel('tutor_front', 'tutor_admin', 'tutor_front_dashboard')();
		} else {
			gulp.parallel(...task_keys)();
		}
	});
});

/**
 * Replace all js sources with the following two
 * 1. tutor-admin.min.js
 * 2. tutor-front.min.js
 * 
 * @since 3.6.0
 */
gulp.task('modify-pot', function (done) {
	const potFilePath = path.resolve(__dirname, 'languages', 'tutor.pot');
	const jsFilesPath = '#: assets/js/tutor-admin.min.js:1\n#: assets/js/tutor-front.min.js:1';

	try {
		const lines = fs.readFileSync(potFilePath, 'utf8').split('\n');
		const updatedLines = [];

		for (let i = 0; i < lines.length; i++) {
			const line = lines[i];

			if (line.startsWith('#:')) {
				const sources = line.substring(2).split(/\s+/).filter(Boolean);

				const phpSources = sources.filter(src => !src.includes('.js'));
				const jsSources = sources.filter(src => src.includes('.js'));

				if (phpSources.length) {
					updatedLines.push(`#: ${phpSources.join(' ')}`);
				}

				if (jsSources.length) {
					updatedLines.push(jsFilesPath);
				}
			} else {
				updatedLines.push(line);
			}
		}

		// Remove duplicate lines
		const deduplicatedLines = updatedLines.filter((line, i, arr) => line !== arr[i - 1]);

		fs.writeFileSync(potFilePath, deduplicatedLines.join('\n'), 'utf8');
		done();
	} catch (err) {
		console.error('Failed to process .pot file:', err);
		done(err);
	}
});

/**
 * Build
 */
gulp.task('clean-zip', function () {
	return gulp
		.src('./' + build_name, {
			read: false,
			allowEmpty: true,
		})
		.pipe(clean());
});

gulp.task('clean-build', function () {
	return gulp
		.src('./build', {
			read: false,
			allowEmpty: true,
		})
		.pipe(clean());
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
		])
		.pipe(gulp.dest('build/tutor/'));
});

const ASSETS_FONTS_DIR = 'assets/fonts';
const V2_LIBRARY_FONTS_DIR = 'v2-library/fonts/fonts/*';

gulp.task('copy-fonts', function () {
	return gulp
		.src(V2_LIBRARY_FONTS_DIR)
		.pipe(gulp.dest(ASSETS_FONTS_DIR));
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

/**
 * Export tasks
 */
exports.build = gulp.series(...task_keys, 'clean-zip', 'clean-build', 'modify-pot', 'copy', 'copy-fonts', 'copy-tutor-droip', 'make-zip', 'clean-build');
exports.sass = gulp.parallel(...task_keys);
exports.default = gulp.parallel(...task_keys, 'watch');
