'use strict';
module.exports = function(grunt) {
    var pkg = grunt.file.readJSON('package.json');
    grunt.initConfig({

        // setting folder templates
        dirs: {
            css: 'assets/css',
            less: 'assets/less',
            fonts: 'assets/fonts',
            images: 'assets/images',
            js: 'assets/js'
        },

        // Compile all .less files.
        less: {

            // one to one
            core: {
                options: {
                    sourceMap: true,
                    sourceMapFilename: 'style.css.map',
                    sourceMapURL: 'style.css.map',
                    sourceMapRootpath: '../../'
                },
                files: {
                    'style.css': '<%= dirs.less %>/style.less',
                    '<%= dirs.css %>/skins/blue.css': '<%= dirs.less %>/skin/blue.less',
                    '<%= dirs.css %>/skins/green.css': '<%= dirs.less %>/skin/green.less',
                    '<%= dirs.css %>/skins/orange.css': '<%= dirs.less %>/skin/orange.less',
                    '<%= dirs.css %>/skins/pink.css': '<%= dirs.less %>/skin/pink.less',
                    '<%= dirs.css %>/skins/purple.css': '<%= dirs.less %>/skin/purple.less',
                    '<%= dirs.css %>/skins/red.css': '<%= dirs.less %>/skin/red.less',
                    '<%= dirs.css %>/skins/sky.css': '<%= dirs.less %>/skin/sky.less',
                    '<%= dirs.css %>/skins/rose-quartz.css': '<%= dirs.less %>/skin/rose-quartz.less'
                }
            },
        },

        watch: {
            less: {
                files: ['<%= dirs.less %>/*.less', '<%= dirs.less %>/skin/*.less' ],
                tasks: ['less:core'],
                options: {
                    livereload: true
                }
            }
        },

        // Generate POT files.
        makepot: {
            target: {
                options: {
                    domainPath: '/languages/', // Where to save the POT file.
                    potFilename: 'dokan.pot', // Name of the POT file.
                    type: 'wp-theme', // Type of project (wp-plugin or wp-theme).
                    potHeaders: {
                        'report-msgid-bugs-to': 'http://wedevs.com/support/forum/theme-support/dokan/',
                        'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
                    }
                }
            }
        },

        // Clean up build directory
        clean: {
            main: ['build/']
        },

        // Copy the plugin into the build directory
        copy: {
            main: {
                src: [
                    '**',
                    '!node_modules/**',
                    '!build/**',
                    '!bin/**',
                    '!.git/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!debug.log',
                    '!phpunit.xml',
                    '!.gitignore',
                    '!.gitmodules',
                    '!npm-debug.log',
                    '!assets/less/**',
                    '!tests/**',
                    '!**/Gruntfile.js',
                    '!**/package.json',
                    '!**/README.md',
                    '!**/export.sh',
                    '!**/*~'
                ],
                dest: 'build/'
            }
        },

        //Compress build directory into <name>.zip and <name>-<version>.zip
        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './build/dokan-theme-v'+ pkg.version + '.zip'
                },
                expand: true,
                cwd: 'build/',
                src: ['**/*'],
                dest: 'dokan'
            }
        },
    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-clean' );
    grunt.loadNpmTasks( 'grunt-contrib-copy' );
    grunt.loadNpmTasks( 'grunt-contrib-compress' );
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );

    grunt.registerTask( 'default', [
        'watch',
    ]);


    grunt.registerTask('release', [
        'makepot',
    ]);

    grunt.registerTask( 'zip', [
        'clean',
        'copy',
        'compress'
    ])
};