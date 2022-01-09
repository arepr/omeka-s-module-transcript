const { src, dest } = require('gulp');
const rename = require('gulp-rename');
const postcss = require('gulp-postcss');

function build() {
    return src('asset/css/style.css')
        .pipe(postcss([
            require('postcss-input-range')(),
            require('postcss-minify')(),
            require('autoprefixer')()
        ]))
        .pipe(rename({ extname: '.min.css' }))
        .pipe(dest('asset/css/'));
}

exports.default = build;