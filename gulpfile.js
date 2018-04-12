"use strict";

var gulp = require('gulp'),

	// Concal all the files piped on the gulp config
	concat = require('gulp-concat'),

	// Strip console, alert, and debugger statements from JavaScript 
	stripdebug = require('gulp-strip-debug'),

	// File content minifier for javascript
	uglify = require('gulp-uglify'),

	cleancss = require('gulp-clean-css'),

	// Runs callback after the preceding process
	callback = require('gulp-callback'),

	// Creates a more readable error logs
	plumber = require('gulp-plumber');


function __fn_optimize_assets(filetype, params)
{
	if (typeof params == 'object') {
		
		var run = 	gulp.src(params.files)
						.pipe(concat(params.file));

		if (filetype === 'css') {
			
			run.pipe(cleancss());
		
		} else {
			
			run.pipe(stripdebug());
			run.pipe(uglify());

		}

		run.pipe(plumber(function(error) {
            console.error(error.message);
            gulp.emit('finish');
        }));

		run.pipe(gulp.dest(params.dest)).pipe(callback(function () {
			console.log('-> ' + params.dest + '/' + params.file);
		}));

		return true;

	}

	console.log('Parameter fucked up.');
	return false;
}

gulp.task('rosies', function () {

	var project_location = 'freelance/rosies/';

	// Vendors
	// --------------------------

	// CSS
	__fn_optimize_assets('css', {
		file: 'vendors.min.css',
		dest: project_location + 'public/assets/build',
		files: [
			project_location + 'public/assets/vendors/materialicons.css',
			project_location + 'public/assets/vendors/bootstrap/css/bootstrap.min.css',
			project_location + 'public/assets/vendors/timepicker/css/bootstrap-timepicker.min.css',
			project_location + 'public/assets/vendors/datepicker/css/bootstrap-datepicker.min.css',
			project_location + 'public/assets/vendors/materialize/css/bootstrap-material-design.css',
			project_location + 'public/assets/vendors/materialize/css/ripples.min.css'
		]
	});

	__fn_optimize_assets('css', {
		file: 'datatables.min.css',
		dest: project_location + 'public/assets/build',
		files: [
			project_location + 'public/assets/vendors/datatables/datatables.min.css',
	        project_location + 'public/assets/vendors/datatables/editor/dataTables.buttons.1.4.0.min.css',
	        project_location + 'public/assets/vendors/datatables/editor/dataTables.select.1.2.2.min.css',
	        project_location + 'public/assets/vendors/datatables/editor/dataTables.responsive.2.1.1.min.css',
	        project_location + 'public/assets/vendors/fileinput/css/fileinput.4.4.8.min.css',
	        project_location + 'public/assets/vendors/fileinput/css/fileinput-rtl.4.4.8.min.css',
		]	
	});

	// Javascript
	__fn_optimize_assets('js', {
		file: 'vendors.min.js',
		dest: project_location + 'public/assets/build',
		files: [
			project_location + 'public/assets/vendors/jquery/jquery-3.3.1.min.js',
			project_location + 'public/assets/vendors/bootstrap/js/bootstrap.min.js',
			project_location + 'public/assets/vendors/timepicker/js/bootstrap-timepicker.min.js',
			project_location + 'public/assets/vendors/datepicker/js/bootstrap-datepicker.min.js',
			project_location + 'public/assets/vendors/materialize/js/ripples.min.js',
			project_location + 'public/assets/vendors/materialize/js/material.min.js',
			project_location + 'public/assets/vendors/chartjs/Chart.2.6.0.min.js'
		]
	});

	__fn_optimize_assets('js', {
		file: 'datatables.min.js',
		dest: project_location + 'public/assets/build',
		files: [
			project_location + 'public/assets/vendors/datatables/datatables.min.js',
	        project_location + 'public/assets/vendors/datatables/editor/dataTables.altEditor.2.0.js',
	        project_location + 'public/assets/vendors/datatables/editor/dataTables.buttons.1.4.0.min.js',
	        project_location + 'public/assets/vendors/datatables/editor/dataTables.select.1.2.2.min.js',
	        project_location + 'public/assets/vendors/datatables/editor/dataTables.responsive.2.1.1.min.js',
	        project_location + 'public/assets/vendors/fileinput/js/fileinput.4.4.8min.js',
	        project_location + 'public/assets/vendors/fileinput/js/purify.min.js',
		]
	});


	// Booking App
	// --------------------------

	// CSS
	__fn_optimize_assets('css', {
		file: 'app.min.css',
		dest: project_location + 'public/modules/booking/build',
		files: [
			project_location + 'public/modules/booking/css/app.css'
		]
	});


	// Javascript
	__fn_optimize_assets('js', {
		file: 'app.min.js',
		dest: project_location + 'public/modules/booking/build',
		files: [
			project_location + 'public/assets/js/plugins.js',
			project_location + 'public/modules/booking/js/app.js'
		]
	});


	// Admin Console App
	// --------------------------

	// CSS
	__fn_optimize_assets('css', {
		file: 'console_login.min.css',
		dest: project_location + 'public/modules/admin/build',
		files: [
			project_location + 'public/modules/admin/css/login.css'
		]
	});
	__fn_optimize_assets('css', {
		file: 'console.min.css',
		dest: project_location + 'public/modules/admin/build',
		files: [
			project_location + 'public/modules/admin/css/console.css'
		]
	});

	// Javascript
	__fn_optimize_assets('js', {
		file: 'console_login.min.js',
		dest: project_location + 'public/modules/admin/build',
		files: [
			project_location + 'public/assets/js/plugins.js',
			project_location + 'public/modules/admin/js/login.js'
		]
	});
	__fn_optimize_assets('js', {
		file: 'console.min.js',
		dest: project_location + 'public/modules/admin/build',
		files: [
			project_location + 'public/assets/js/plugins.js',
			project_location + 'public/modules/admin/js/helpers.js',
			project_location + 'public/modules/admin/js/crud.js',
			project_location + 'public/modules/admin/js/console.js'
		]
	});
});
