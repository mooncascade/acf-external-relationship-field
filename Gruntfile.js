module.exports = function (grunt) {
  'use strict';
  
  grunt.initConfig({

    pkg : grunt.file.readJSON('package.json'),

    addtextdomain : {
      options : {
        textdomain : 'acf-external-relationship',
      },
      target : {
        files : {
          src : [ '*.php', '**/*.php', '!node_modules/**', '!php-tests/**',
              '!bin/**' ]
        }
      }
    },

    wp_readme_to_markdown : {
      your_target : {
        files : {
          'README.md' : 'readme.txt'
        }
      },
    },

    makepot : {
      target : {
        options : {
          domainPath : '/languages',
          mainFile : 'acf-external-relationship.php',
          potFilename : 'acf-external-relationship.pot',
          potHeaders : {
            poedit : true,
            'x-poedit-keywordslist' : true
          },
          type : 'wp-plugin',
          updateTimestamp : true
        }
      }
    },
  });

  grunt.loadNpmTasks('grunt-wp-i18n');
  grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
  grunt.registerTask('i18n', [ 'addtextdomain', 'makepot' ]);
  grunt.registerTask('readme', [ 'wp_readme_to_markdown' ]);

  grunt.util.linefeed = '\n';
};
