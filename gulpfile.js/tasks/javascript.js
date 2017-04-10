var config = require('../config')
if(!config.tasks.javascript) return

var config  = require('../lib/webpack-multi-config')('development')
var gulp    = require('gulp')
var logger  = require('../lib/compileLogger')
var webpack = require('webpack')

var webpackProductionTask = function(callback) {
  webpack(config, function(err, stats) {
    logger(err, stats)
    if(callback !== undefined)callback()
  })
}

gulp.task('javascript', webpackProductionTask)
module.exports = webpackProductionTask
