/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
module.exports = function(grunt) {

  require('time-grunt')(grunt);
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    assetsDir: grunt.option('assets-dir', 'Resources/assets'),
    publicDir: grunt.option('public-dir', 'Resources/public'),
    lessDir: '<%= assetsDir %>/less',

    sync: {
      main: {
        files: [{
          expand: true,
          cwd: '<%= assetsDir %>',
          src: [ '**/*', '**/.*','.htaccess', '!less/**', '!less' ],
          dest: '<%= publicDir %>'
        }],
        //pretend: true, // Don't do any disk operations - just write log
        //verbose: true, // Display log messages when copying files

        ignoreInDest: ['css/**', 'less', 'js/main.built.js'],
        updateAndDelete: true
      }
    },

    less: {
      main : {
        options: {
          sourceMap: true,
          outputSourceFiles: true,
          sourceMapURL: 'main.css.map',
          sourceMapFilename: '<%= publicDir %>/css/main.css.map'
        },
        files: {
          '<%= publicDir %>/css/main.css': '<%= lessDir %>/main.less'
        }
      }
    },

    cssmin: {
      main: {
        files: {
          '<%= publicDir %>/css/main.min.css': '<%= publicDir %>/css/main.css'
        }
      }
    },

    requirejs: {
      main: {
        options: {
          baseUrl: "<%= assetsDir %>/js/",
          optimize: "uglify",
          findNestedDependencies: true,
          wrapShim: true,
          name: "main",
          mainConfigFile: "<%= assetsDir %>/js/main.js",
          out: "<%= publicDir %>/js/main.built.js"
        }
      }
    },

    jshint: {
      options: {
        jshintrc: true
      },
      main : [
        'Gruntfile.js',
        '<%=assetsDir%>/js/**/*.js',
        '!<%=assetsDir%>/js/main.built.js',
        '!<%=assetsDir%>/js/vendor/**'
      ]
    },

    revision: {
      options: {
        property: 'meta.revision',
        ref: 'HEAD',
        short: true
      }
    },

    'string-replace': {
      dist: {
        files: {
          '<%= publicDir %>/js/modules/Footer/templates/footer.ejs' : '<%= assetsDir %>/js/modules/Footer/templates/footer.ejs'
        },
        options: {
          replacements: [{
            pattern: '{{revision}}',
            replacement: '<%= meta.revision %>'
          }]
        }
      }
    },

    watch: {
      css: {
        files: ['<%= publicDir %>/css/main.css'],
        options: {
          livereload: true
        }
      },
      main: {
        files: '<%= assetsDir %>/**',
        tasks: ['sync', 'html']
      },
      less : {
        files: '<%= lessDir %>/**',
        tasks: ['css']
      }
    }

  });

  grunt.registerTask('js', ['jshint', 'requirejs']);
  grunt.registerTask('css', ['less', 'cssmin']);
  grunt.registerTask('html', ['revision', 'string-replace']);
  grunt.registerTask('default', ['js','sync', 'css', 'html']);

};
