module.exports = function(grunt) {

	/**
	 * Project Configuration
	 ===================================*/
	grunt.initConfig({
		pkg			: grunt.file.readJSON( 'package.json' ),
		datetime	: Date.now(),
		banner			: '/*!\n' +
		'Package		: <%= pkg.title || pkg.name %> (<%= pkg.homepage %>)\n' +
		'Version		: <%= pkg.version %>\n' +
		'Last updated	: <%= grunt.template.today("yyyy-mm-dd") %>\n' +
		'* Author		: <%= pkg.author.name %>;\n' +
		'* Website		: <%= pkg.author.website %> */\n',

		/**
		 * JS Hint
		 *
		 * Configures JSHint and adds commonly used globals
		 * within the bootstrap project template.
		 ****************************************************** */
		jshint : {
			options : {
				globalstrict: true,
				curly		: false,
				eqeqeq		: true,
				eqnull		: true,
				browser		: true,
				devel		: true,
				loopfunc	: true,
				globals		: {
					$			: true,
					jQuery		: true,
					CONFIG		: true
				}
			},

			all : [
				'../../www/assets/js/public/app.js'
			]
		},

		/**
		 * Concat
		 *
		 * Will concatenate vendor libraries and application
		 * files into a single file for uglification.
		 ****************************************************** */
		concat : {
			vendor_app : {
				src : [
					'../../www/assets/js/common/jquery/jquery-1.10.2.min.js',
					'../../www/assets/js/common/jquery/jquery-migrate-1.2.1.min.js',
					'../../www/assets/js/common/jquery/plugins/*.js',
					'../../www/assets/js/common/bootstrap/*.js',
					'../../www/assets/js/common/underscore/*.js'
				],
				dest : '../temp/<%=pkg.prepend_to_file %>lib.js'
			},
			app : {
				src : [
					'../../www/assets/js/public/app.js'
				],
				dest : '../temp/<%=pkg.prepend_to_file %>app.js'
			}
		},

		/**
		 * Uglify
		 *
		 * Compresses concatenated JS files.
		 ****************************************************** */
		uglify : {
			options : {
				banner		: '<%= banner %>',
				mangle		: false,
				exportAll	: true
			},
			vendor_app : {
				src		: '../temp/<%=pkg.prepend_to_file %>lib.js',
				dest	: '../../www/assets/js/<%=pkg.prepend_to_file %>lib.min.js'
			},
			app : {
				src		: '../temp/<%=pkg.prepend_to_file %>app.js',
				dest	: '../../www/assets/js/<%=pkg.prepend_to_file %>app.min.js'
			}
		},

		/**
		 * Clean
		 *
		 * Clean temp/source folders pre/post task.
		 ****************************************************** */
		clean : {
			build : {
				options : {
					force : true
				},
				src : ['../temp/*']
			},
			sass_build : {
				options : {
					force : true
				},
				src : ['../compass/.sass-cache/*'],
			},
			css : {
				options : {
					force : true
				},
				src : ['../../www/assets/css/*']
			}
		},

		/**
		 * Compass
		 *
		 * Compiler for Compass. The configuration file is at:
		 * /build/compass/config.rb
		 * to configure & set Compass related settings.
		 ****************************************************** */
		compass : {
			development : {
				options : {
					config : '../compass/config.rb'
				}
			}
		},

		/**
		 * Watch
		 *
		 * Build script watch task to compile as changes are
		 * detected.
		 ****************************************************** */
		watch : {
			application_code : {
				files : [ '../../www/assets/js/public/app.js' ],
				tasks : [ 'main' ]
			},
			compass : {
				files : [ '../compass/sass/**/*' ],
				tasks : [ 'compass', 'clean:sass_build' ]
			}
		}
	});

	/**
	 * Load Grunt plugins
	 */
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-compass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	/**
	 * Task definitions
	 *
	 * Defined tasks below.
	 */
	grunt.registerTask( 'main',			[ 'jshint', 'concat:app', 'uglify:app', 'compass', 'clean:build', 'clean:sass_build' ] );
	grunt.registerTask( 'libs',			[ 'jshint', 'concat:vendor_app', 'uglify:vendor_app', 'clean:build' ] );
};