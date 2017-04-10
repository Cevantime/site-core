var gulp       = require('gulp')
var config     = require('../../config')
var revReplace = require('gulp-rev-replace')
var path       = require('path')

// 5) Update asset references in HTML
gulp.task('update-html', function(){
	console.log(path.join(config.root.dest, "/rev-manifest.json"));
  var manifest = gulp.src(path.join(config.root.dest, "/rev-manifest.json"))
  gulp.src(path.join(config.root.src, '/application/modules/*'))
    .pipe(revReplace({manifest: manifest,replaceInExtensions:['.blade']}))
    .pipe(gulp.dest(path.join(config.root.src, '/application/newmod/')));
  return gulp.src(path.join(config.root.src, '/application/views/***'))
    .pipe(revReplace({manifest: manifest,replaceInExtensions:'.blade'}))
    .pipe(gulp.dest(path.join(config.root.src, '/application/new/')))
})
