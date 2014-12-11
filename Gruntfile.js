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

  require('load-grunt-tasks')(grunt);


  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    webRoot: grunt.option('webRoot', '../../../front'),
    srcRoot: grunt.option('src', 'Resources/front'),
    lessFolder: '<%= srcRoot %>/assets/less',

    sync: {
      main: {
        files: [{
          expand: true,
          cwd: '<%= srcRoot %>',
          src: [ '**/*', '**/.*','.htaccess', '!assets/less/**' ],
          dest: '<%= webRoot %>'
        }],
        //pretend: true, // Don't do any disk operations - just write log
        //verbose: true, // Display log messages when copying files

        ignoreInDest: "assets/css/**",
        updateAndDelete: true
      }
    },

    less: {
      main : {
        files: {
          '<%= webRoot %>/assets/css/main.css': '<%= lessFolder %>/main.less'
        }
      }
    },

    watch: {
      main: {
        files: '<%= srcRoot %>/**',
        tasks: ['sync']
      },
      less : {
        files: '<%= lessFolder %>/**',
        tasks: ['less']
      }
    }

  });

  // Default task(s).
  grunt.registerTask('default', ['sync', 'less']);

};
