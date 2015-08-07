'use strict';
module.exports = function ( grunt ) {

	// load all grunt tasks matching the `grunt-*` pattern
	// Ref. https://npmjs.org/package/load-grunt-tasks
	require( 'load-grunt-tasks' )( grunt );

	grunt.initConfig( {
		// SCSS and Compass
		// Ref. https://npmjs.org/package/grunt-contrib-compass
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! \n * rtBiz Helpdesk JavaScript Library \n * @package rtBiz Helpdesk \n */'
			},
			frontend: {
				src: [
					'public/js/rtbiz-ideas-public.js',
				],
				dest: 'public/js/rtbiz-ideas-public-min.js'
			},
		},
		compass: {
			frontend: {
				options: {              // Target options
					sassDir: 'public/css/scss',
					cssDir: 'public/css/'
				}
			}
		},
		jshint: {
			all: ['Gruntfile.js', 'public/js/*.js', 'admin/js/**/*.js']
		},
		watch: {
			compass: { files: [ '**/*.{scss,sass}' ],
				tasks: [ 'compass' ]
			},
			uglify: {
				files: [ '<%= uglify.frontend.src %>' ],
				tasks: [ 'jshint', 'uglify' ]
			},
		}
	} );

	// Register Task
	grunt.registerTask( 'default', [ 'watch' ] );
};
