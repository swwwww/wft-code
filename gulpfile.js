/*global require*/
var gulp = require('gulp');
var less = require('gulp-less');
var path = require('path');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var notify = require('gulp-notify');
var plumber = require('gulp-plumber');

gulp.task('less', function () {

    return gulp.src(['./static/css/lib/*.less', './static/css/ad/sale/*.less','./static/css/coupon/m/*.less','./static/css/ad/grasp/*.less',
            './static/css/ad/scan/*.less','./static/css/ad/god/*.less','./static/css/ui/*.less',
            './static/css/ad/turntable/*.less', './static/css/mobile/*.less','./static/css/commodity/m/*.less',
            './static/css/discover/m/*.less',  './static/css/play/m/*.less', './static/css/ad/battle/*.less',
            './static/css/order/m/*.less','./static/css/user/m/*.less', './static/css/cash/m/*.less',
            './static/css/comment/m/*.less', './static/css/member/m/*.less','./static/css/mobile/*.less',
            './static/css/play/m/*.less', './static/css/order/m/*.less','./static/css/user/m/*.less',
            './static/css/recommend/m/*.less','./static/css/ticket/m/*.less','./static/css/collect/*.less',
        './static/css/kidsplayers/m/*.less','./static/css/admin/business/*.less'])
        .pipe(plumber({errorHandler: notify.onError('Error: <%= error.message %>')}))
        .pipe(less())
        .pipe(gulp.dest(function(file){
            return path.dirname(file.path);
        }));
});


gulp.task('js', function () {
    var files = [
        // request
        'static/js/util/dao.js'
    ];

    return gulp.src(files)
        .pipe(uglify())
        .pipe(concat('main.js'))
        .pipe(gulp.dest('static/js'));
});

gulp.task('watch', function () {
    return gulp.watch(['./static/css/lib/*.less', './static/css/ad/sale/*.less','./static/css/coupon/m/*.less','./static/css/ad/grasp/*.less',
        './static/css/ad/scan/*.less','./static/css/ad/god/*.less','./static/css/ui/*.less',
        './static/css/ad/turntable/*.less', './static/css/mobile/*.less','./static/css/commodity/m/*.less',
        './static/css/discover/m/*.less',  './static/css/play/m/*.less', './static/css/ad/battle/*.less',
        './static/css/order/m/*.less','./static/css/user/m/*.less','./static/css/cash/m/*.less',
        './static/css/comment/m/*.less','./static/css/member/m/*.less','./static/css/kidsplayers/m/*.less',
        './static/css/play/m/*.less', './static/css/order/m/*.less','./static/css/user/m/*.less',
        './static/css/recommend/m/*.less','./static/css/ticket/m/*.less','./static/css/mobile/*.less','./static/css/admin/business/*.less'], ['less']);
});

gulp.task('default', ['less', 'js', 'watch']);

