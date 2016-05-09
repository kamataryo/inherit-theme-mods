var gulp    = require('gulp');
var coffee  = require('gulp-coffee');
var plumber = require('gulp-plumber');
var notify  = require('gulp-notify');
var gettext = require('gulp-gettext');

gulp.task('coffee', function(){
    gulp.src('./assets/*.coffee')
        .pipe(plumber({errorHandler: notify.onError('<%= error.message %>')}))
        .pipe(coffee({bare:false}))
        .pipe(gulp.dest('./assets'));
});

gulp.task('gettext', function(){
    gulp.src('./languages/*.po')
        .pipe(gettext())
        .pipe(gulp.dest('./languages/'));
});

gulp.task('watch', ['coffee', 'gettext'], function(){
    gulp.watch(['./assets/*.coffee','./languages/*.po'], ['coffee','gettext']);
});
