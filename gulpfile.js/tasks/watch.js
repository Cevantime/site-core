var config = require('../config')
var gulp   = require('gulp')
var path   = require('path')
var watch  = require('gulp-watch')
var browserSync = require('browser-sync')

var watchTask = function() {
  var watchableTasks = ['fonts', 'iconFont', 'images', 'svgSprite','html', 'css', 'javascript']

  watchableTasks.forEach(function(taskName) {
    var task = config.tasks[taskName]
    if(task) {
      var glob = path.join(config.root.src, task.src, '**/*.{' + task.extensions.join(',') + '}')
      watch(glob, function() {
       require('./' + taskName)()
      })
    }
  })
  
  watch(path.join(config.root.src,'application/views/***/**/*'),browserSync.reload)
  watch(path.join(config.root.src,'application/controllers/***/**/*'),browserSync.reload)
  watch(path.join(config.root.src,'application/models/***/**/*'),browserSync.reload)
}

gulp.task('watch', ['browserSync'], watchTask)
module.exports = watchTask
