var gulp = require('gulp'),
    uglify = require('gulp-uglify'),
    minifyCss = require('gulp-minify-css'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),
    compass = require('gulp-compass'),
    imagemin = require('gulp-imagemin'),
    cache = require('gulp-cache'),
    clean = require('gulp-clean'),
    rev = require('gulp-rev'),
    revCollector = require('gulp-rev-collector'),
    requirejsOptimize = require('gulp-requirejs-optimize'),
    browserSync = require('browser-sync'),
    reload = browserSync.reload;

/**
* 配置选项
*/
var config = {
  cssUrl : 'sources/sass/*.scss',
  jsUrl : 'sources/javascripts/only/*.js',
  htmlUrl : ['sources/**/*.html','sources/index.html'],
  imageUrl : 'sources/images/**/*',
}

/**
* css文件处理
*/
gulp.task('buildCss',function(){
  return gulp.src( config.cssUrl )
      .pipe(compass({
      config_file: 'config.rb',
      css: 'sources/sass',
      sass: 'sources/sass'
    }))
    .pipe(concat('main.css'))
    .pipe(minifyCss())
    .pipe(rename({basename: 'main', suffix: '.min'}))
    .pipe(rev())
    .pipe(gulp.dest('src/css/market'))
    .pipe(rev.manifest())
    .pipe(gulp.dest('sources/rev/css'));
});

/**
* js文件处理
*/
gulp.task('buildJs',['move'],function(){
  return gulp.src( config.jsUrl )
    //合并require模块js
    .pipe(requirejsOptimize())
    //压缩js
    .pipe(uglify())
    .pipe(rev())
    //输出文件
    .pipe(gulp.dest('src/js'))
    .pipe(rev.manifest())
    .pipe(gulp.dest('sources/rev/js'));
});

/**
 * image压缩
 */
gulp.task('buildImg', function() {
  return gulp.src( config.imageUrl )
    .pipe(imagemin({
        distgressive: true,
        progressive: true,
        interlaced: true,
        svgoPlugins: [{removeViewBox: false}]
    }))
    .pipe(rev())
    .pipe(gulp.dest('src/images'))
    .pipe(rev.manifest())
    .pipe(gulp.dest('sources/rev/img'));
});

/**
 * html
 */
gulp.task('buildHtml', function() {
  return gulp.src( config.htmlUrl )
    .pipe(gulp.dest('src'));
});

gulp.task('rimg', ['buildImg'], function(){
  return gulp.src(['sources/rev/img/*.json', 'src/**/*'])
    .pipe(revCollector())
    .pipe(gulp.dest('src'))
    .pipe(reload({ stream:true }));
});

gulp.task('rcss', ['buildCss'], function(){
  return gulp.src(['sources/rev/css/*.json', 'src/**/*'])
    .pipe(revCollector())
    .pipe(gulp.dest('src'))
    .pipe(reload({ stream:true }));
});

gulp.task('rjs', ['buildJs'], function(){
  return gulp.src(['sources/rev/js/*.json', 'src/**/*'])
    .pipe(revCollector())
    .pipe(gulp.dest('src'))
    .pipe(reload({ stream:true }));
});

/**
 * browserSync服务监听
 *
 */
gulp.task('serve',['rimg', 'rjs', 'rcss', 'buildHtml'], function() {
  browserSync({
    server: {
      baseDir: './src/'
    }
  });

  //监听image文件
  gulp.watch( config.imageUrl, ['rimg']);
  //监听scss文件
  gulp.watch( config.cssUrl, ['rcss']);
  //监听js文件
  gulp.watch( config.jsUrl, ['rjs']);
  //监听html文件
  gulp.watch( config.htmlUrl, ['buildHtml']);
});

/**
 * 移动requirejs主文件
 */
gulp.task('move', function() {
  return gulp.src(['sources/javascripts/require.js'])
    .pipe(gulp.dest('src/js'));
});

/**
 * 版本号文件替换
 */
gulp.task('rev',['buildImg', 'buildJs', 'buildCss', 'buildHtml'], function() {
  return gulp.src(['sources/rev/**/*.json', 'src/**/*'])  //- 读取 rev-manifest.json 文件以及需要进行css名替换的文件
      .pipe(revCollector())  //- 执行文件内css名的替换
      .pipe(gulp.dest('src'))
      .pipe(reload({ stream:true }));  //- 替换后的文件输出的目录
});

/**
 * 清理文件
 */
gulp.task('clean', function () {
  return gulp.src(['src/*','sources/rev/*'], {read: false})
    .pipe(clean());
});

/**
 * default
 */
gulp.task('default',['clean'], function() {
  gulp.run('serve');
});