var gulp = require( 'gulp' ),
    replace = require( 'gulp-replace' ),
    gettext = require( 'gulp-gettext' );

/**
 * Compile .po files to .mo
 */
var poFiles = ['./languages/**/*.po'];
gulp.task( 'po2mo', function () {
    return gulp.src( poFiles )
        .pipe( gettext() )
        .pipe( gulp.dest( function (file) {
            return file.base;
        } ) );
} );

/**
 * Watch tasks.
 *
 * Init watches by calling 'gulp' in terminal.
 */
gulp.task( 'default', gulp.series( gulp.parallel( 'po2mo' ), watchers = ( done ) => {
    gulp.watch( poFiles, gulp.series( 'po2mo' ) );

    done();
} ) );

/**
 * Clear build/ folder.
 */
var del = require( 'del' ), // deletion
    concat = require( 'gulp-concat' ); // concat files

gulp.task( 'clear:build', function(done) {
    del.sync( 'dist/**/*' );
    done();
} );

gulp.task( 'build', gulp.series( 'clear:build', gulp.parallel( 'po2mo' ), building = (done) => {
    // collect all needed files
    gulp.src( [
        '**/*',
        // ... but:
        '!**/*.scss',
        '!**/*.css', // will be collected see next function
        '!**/*.map',
        '!*.md',
        '!LICENSE',
        '!readme.txt',
        '!gulpfile.js',
        '!package.json',
        '!package-lock.json',
        '!.csscomb.json',
        '!.gitignore',
        '!node_modules{,/**}',
        '!dist{,/**}',
        '!assets{,/**}'
    ] ).pipe( gulp.dest( 'dist/' ) );

    // collect css files
    gulp.src( [ '**/*.css', '!node_modules{,/**}' ] )
        .pipe( gulp.dest( 'dist/' ) );

    // concat files for WP's readme.txt
    // manually validate output with https://wordpress.org/plugins/about/validator/
    gulp.src( [ 'readme.txt', 'README.md', 'CHANGELOG.md' ] )
        .pipe( concat( 'readme.txt' ) )
        // remove screenshots
        // todo: scrennshot section for WP's readme.txt
        .pipe( replace( /\n\!\[image\]\([^)]+\)\n/g, '' ) )
        // WP markup
        .pipe( replace( /#\s*(Changelog)/g, "## $1" ) )
        .pipe( replace( /##\s*([^(\n)]+)/g, "== $1 ==" ) )
        .pipe( replace( /==\s(Unreleased|[0-9\s\.-]+)\s==/g, "= $1 =" ) )
        .pipe( replace( /#\s*[^\n]+/g, "== Description ==" ) )
        .pipe( gulp.dest( 'dist/' ) );

    done();
} ) );
