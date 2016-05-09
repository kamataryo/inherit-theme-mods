var gulp    = require('gulp');
var coffee  = require('gulp-coffee');
var plumber = require('gulp-plumber');
var notify  = require('gulp-notify');

gulp.task('coffee', function(){
    gulp.src('./assets/*.coffee')
        .pipe(plumber({errorHandler: notify.onError('<%= error.message %>')}))
        .pipe(coffee({bare:false}))
        .pipe(gulp.dest('./assets'));
});

gulp.task('watch', ['coffee'], function(){
    gulp.watch('./assets/*.coffee', ['coffee']);
});
