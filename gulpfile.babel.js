'use strict';

import path from 'path';
import gulp from 'gulp';
import rename from 'gulp-rename';
import sass from 'gulp-sass';
import webpack from 'webpack-stream';
import replace from'gulp-replace';
import inject from 'gulp-inject-string';
import webpackConfig from './webpack.config.babel';
import autoprefixer from 'gulp-autoprefixer';
import stylelint from 'gulp-stylelint';
import sourcemaps from 'gulp-sourcemaps';


/**
 * Compile SCSS/SASS files
 */
const scssFiles = [
    path.join(__dirname, '/client/src/styles/styles.scss')
];


gulp.task('scss', function () {
    gulp.src(scssFiles)
        .pipe(sourcemaps.init())
        .pipe(sass.sync({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(autoprefixer())
        .pipe(rename({extname: '.min.css'}))
        .pipe(sourcemaps.write('../sourcemaps'))
        .pipe(gulp.dest('./client/dist'));
});

gulp.task('scss:w', function () {
    gulp.watch([
        path.join(__dirname, '/client/src/styles/styles/*.scss'),
        path.join(__dirname, '/client/src/styles/styles/**/*.scss')
        ], ['scss']);
});

/**
 *   Bundle JS files
 *   Need browserify or webpack to work
 */
const jsFiles = path.join(__dirname, 'client/src/bundles/*.*');

gulp.task('js', function () {
    return gulp.src(jsFiles)
        .pipe(webpack(webpackConfig))
        .pipe(gulp.dest('./client/dist/bundle'));
});



gulp.task('js:w', function () {
    gulp.watch(jsFiles, ['js']);
});

gulp.task('watch', function () {
    gulp.watch([
        jsFiles,
        path.join(__dirname, '/client/src/styles/styles/*.scss'),
        path.join(__dirname, '/client/src/styles/styles/**/*.scss')
    ], ['default']);
});

gulp.task('default', ['scss', 'js']);