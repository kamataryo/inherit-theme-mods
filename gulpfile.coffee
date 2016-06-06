gulp    = require 'gulp'
coffee  = require 'gulp-coffee'
plumber = require 'gulp-plumber'
notify  = require 'gulp-notify'
gettext = require 'gulp-gettext'

gulp.task 'coffee', ->
    gulp.src './assets/*.coffee'
        .pipe plumber {errorHandler: notify.onError('<%= error.message %>')}
        .pipe coffee bare:false
        .pipe gulp.dest './assets'

gulp.task 'gettext', ->
    gulp.src './languages/*.po'
        .pipe gettext()
        .pipe gulp.dest './languages/'

gulp.task 'build',['coffee', 'gettext']

gulp.task 'watch', ['build'], ->
    gulp.watch ['./assets/*.coffee','./languages/*.po'], ['build']
