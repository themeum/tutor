var gulp = require("gulp"),
	sass = require("gulp-sass"),
	sourcemaps = require('gulp-sourcemaps'),
	rename = require("gulp-rename"),
	prefix = require("gulp-autoprefixer"),
	plumber = require("gulp-plumber"),
	notify = require("gulp-notify"),
	wpPot = require('gulp-wp-pot'),
	clean = require("gulp-clean"),
	zip = require("gulp-zip"),
	fs = require('fs'),
	path = require('path'),
	build_name = 'tutor-' + require('./package.json').version + '.zip';


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
	tutor_front: {src: "assets/scss/front/index.scss", mode: 'expanded', destination: 'tutor-front.css'},
	tutor_front_min: {src: "assets/scss/front/index.scss", mode: 'compressed', destination: 'tutor-front.min.css'},
	
	tutor_admin: {src: "assets/scss/admin-dashboard/index.scss", mode: 'expanded', destination: 'tutor-admin.css'},
	tutor_admin_min: {src: "assets/scss/admin-dashboard/index.scss", mode: 'compressed', destination: 'tutor-admin.min.css'},
	
	tutor_setup: {src: "assets/scss/admin-dashboard/tutor-setup.scss", mode: 'expanded', destination: 'tutor-setup.css'},
	tutor_setup_min: {src: "assets/scss/admin-dashboard/tutor-setup.scss", mode: 'compressed', destination: 'tutor-setup.min.css'},

	tutor_v2: {src: "v2-library/_src/scss/main.scss", mode: 'expanded', destination: 'tutor-v2.css'},
	tutor_v2_min: {src: "v2-library/_src/scss/main.scss", mode: 'compressed', destination: 'tutor-v2.min.css'},
	
	tutor_front_dashboard: {src: "assets/scss/frontend-dashboard/index.scss", mode: 'expanded', destination: 'tutor-frontend-dashboard.css'},
	tutor_front_dashboard_min: {src: "assets/scss/frontend-dashboard/index.scss", mode: 'compressed', destination: 'tutor-frontend-dashboard.min.css'},

	tutor_course_builder: {src: "assets/scss/course-builder/index.scss", mode: 'expanded', destination: 'tutor-course-builder.css'},
	tutor_course_builder_min: {src: "assets/scss/course-builder/index.scss", mode: 'compressed', destination: 'tutor-course-builder.min.css'},

	v2_scss:{src: "v2-library/_src/scss/main.scss", mode: 'compressed', destination: 'main.min.css', dest_path: 'v2-library/bundle'},
	v2_scss_docz:{src: "v2-library/_src/scss/main.scss", mode: 'compressed', destination: 'main.min.css', dest_path: '.docz/static/v2-library/bundle'}
};


var task_keys = Object.keys(scss_blueprints);

for(let task in scss_blueprints) {
	
	let blueprint = scss_blueprints[task];
	
	gulp.task(task, function () {
		return gulp.src(blueprint.src)
			.pipe(plumber({errorHandler: onError}))
			.pipe(sourcemaps.init({loadMaps: true, largeFile:true}))
			.pipe(sass({outputStyle: blueprint.mode}))
			.pipe(prefix(prefixerOptions))
			.pipe(rename(blueprint.destination))
			.pipe(sourcemaps.write('.', {addComment: process.env._GULP_ENV!='build'}))
			.pipe(gulp.dest(blueprint.dest_path || "assets/css"));
	});
}

var added_texts = [];
const regex = /__\(\s*(['"])((?:(?!(?<!\\)\1).)+)\1(?:,\s*(['"])((?:(?!(?<!\\)\3).)+)\3)?\s*\)/ig;
const js_files = ['tutor-front', 'tutor-admin', 'tutor-course-builder', 'tutor-setup'].map(f=>'assets/js/'+f+'.js:1').join(', ');
function i18n_makepot(callback, target_dir) {

	const parent_dir = target_dir || __dirname;
	var translation_texts = '';

	// Loop through JS files inside js directory
	fs.readdirSync(parent_dir).forEach( function(file_name) {

		if(file_name=='node_modules' || file_name.indexOf('.')===0) {
			return;
		}

		var full_path = parent_dir+'/'+file_name;
		var stat = fs.lstatSync(full_path);

		if(stat.isDirectory()) {
			i18n_makepot(null, full_path);
			return;
		}

		// Make sure only js extension file to process
		if(stat.isFile() && path.extname(file_name)=='.js' && full_path.indexOf('assets/react')>-1)
		{
			var codes = fs.readFileSync(full_path).toString();
			var lines = codes.split('\n');
			
			// Loop through every single line in the JS file
			for(var i=0; i<lines.length; i++) {

				var found = lines[i].match(regex);
				!Array.isArray(found) ? found=[] : 0;

				// Loop through found translations
				for(var n=0; n<found.length; n++) {
					// Parse the string

					var string = found[n];
					var first_quote = string.indexOf("'")+1;
					var second_quote = string.indexOf("'", first_quote);
					var text = string.slice(first_quote, second_quote);

					if(added_texts.indexOf(text)>-1) {
						// Avoid duplicate entry
						continue;
					}

					added_texts.push(text);
					translation_texts+= 
						'\n#: ' + js_files 
						+ '\nmsgid "'+text
						+'"\nmsgstr ""' 
						+ '\n'; 
				}
			}
		}
	});

	// Finally append the texts to the pot file
	var text_domain = path.basename(__dirname);
	fs.appendFileSync(__dirname + '/languages/' + text_domain.toLowerCase() + '.pot', translation_texts);

	callback ? callback() : 0;
}

gulp.task("watch", function () {
	gulp.watch("./**/*.scss", gulp.series(...task_keys));
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
	return gulp.src("./"+build_name, {
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
			"!./assets/react/**",
			"!./assets/scss/**",
			"!./assets/.sass-cache",
			"!./node_modules/**",
			"!./v2-library/**",
			"!./.docz/**",
			"!./**/*.zip",
			"!.github",
			"!./readme.md",
			"!.DS_Store",
			"!./**/.DS_Store",
			"!./LICENSE.txt",
			"!./*.lock",
			"!./*.js",
			"!./*.json",
		])
		.pipe(gulp.dest("build/tutor/"));
});

gulp.task("make-zip", function () {
	return gulp.src("./build/**/*.*").pipe(zip(build_name)).pipe(gulp.dest("./"));
});

/**
 * Export tasks
 */
exports.build = gulp.series(
	...task_keys,
	"clean-zip",
	"clean-build",
	"makepot",
	i18n_makepot,
	"copy",
	"make-zip",
	"clean-build"
);
exports.sass = gulp.series(...task_keys);
exports.default = gulp.series(...task_keys, "watch");