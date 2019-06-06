/**
 *  Give Gulp File
 *
 *  Used for automating development tasks.
 */

/* Modules (Can be installed with npm install command using package.json)
 ------------------------------------- */
var gulp = require( 'gulp' ),
	sort = require( 'gulp-sort' ),
	wpPot = require( 'gulp-wp-pot' ),
	checktextdomain = require( 'gulp-checktextdomain' ),
	readme = require( 'gulp-readme-to-markdown' );

/* POT file task
 ------------------------------------- */
gulp.task( 'pot', function() {
	return gulp.src( '**/*.php' )
		.pipe( sort() )
		.pipe( wpPot( {
			package: 'Give - Email Reports',
			domain: 'give-email-reports', //textdomain
			destFile: 'give-email-reports.pot',
			bugReport: 'https://github.com/impress-org/give-email-reports/issues/new',
			lastTranslator: '',
			team: 'GiveWP <info@givewp.com>'
		} ) )
		.pipe( gulp.dest( 'languages' ) );
} );

/* Text-domain task
 ------------------------------------- */
gulp.task( 'textdomain', function() {
	var options = {
		text_domain: 'give-email-reports',
		keywords: [
			'__:1,2d',
			'_e:1,2d',
			'_x:1,2c,3d',
			'esc_html__:1,2d',
			'esc_html_e:1,2d',
			'esc_html_x:1,2c,3d',
			'esc_attr__:1,2d',
			'esc_attr_e:1,2d',
			'esc_attr_x:1,2c,3d',
			'_ex:1,2c,3d',
			'_n:1,2,4d',
			'_nx:1,2,4c,5d',
			'_n_noop:1,2,3d',
			'_nx_noop:1,2,3c,4d'
		],
		correct_domain: true
	};
	return gulp.src( '**/*.php' )
		.pipe( checktextdomain( options ) );
} );

/* Convert WordPress readme file to readme.md
 ------------------------------------- */
gulp.task( 'readme', function() {
	gulp.src( [ 'readme.txt' ] )
		.pipe( readme( {
			details: false,
			screenshot_ext: [
				'jpg',
				'jpg',
				'png'
			],
			extract: {}
		} ) )
		.pipe( gulp.dest( '.' ) );
} );

/* Default Gulp task
 ------------------------------------- */
gulp.task( 'default', function() {
	// Run all the tasks!
	gulp.start( 'textdomain', 'pot', 'readme' );
} );
