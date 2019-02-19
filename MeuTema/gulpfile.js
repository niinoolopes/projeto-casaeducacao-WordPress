// for gulp
const gulp = require('gulp');
const gulpSequence = require('gulp-sequence').use(gulp);
// for server
const browserSync = require('browser-sync').create();
// for css
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
// for javascript
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
// for comand line
const shell = require('gulp-shell');
// for process
const concat = require('gulp-concat');
const plumber = require('gulp-plumber');

const root = {
  dev: 'app',
  deploy: 'docs'
};
const structure = {
  tipe_html: 'html',
  allFile_html: '**/*.html',
  tipe_php: 'php',
  allFile_php: '**/*.php'
}
const files = {
  past: 'assets',
  images: 'images',
  icons: 'icons',
  fonts: 'fonts'
}
const css = {
  name: 'estilo.css',
  name_min: 'estilo-min.css',
  past: 'styles',
  src: 'scss',
  allFile: '**/*.scss',
}
const javascript = {
  name: 'main.js',
  name_min: 'main-min.js',
  past: 'scripts',
  src: 'src',
  allFile: '**/*.js',
}

gulp.task('serve', function () {
  browserSync.init({
    server: {
      baseDir: root.dev,
    }
  });
});





// CREATE

gulp.task('create', shell.task(
  [
    // FOLDERS
    `mkdir "${root.teste}" `,
    `mkdir "${root.teste}/${files.past}" `,
    `mkdir "${root.teste}/${files.past}/${files.images}" `,
    `mkdir "${root.teste}/${files.past}/${files.icons}" `,
    `mkdir "${root.teste}/${files.past}/${files.fonts}" `,
    `mkdir "${root.teste}/${javascript.past}" `,
    `mkdir "${root.teste}/${javascript.past}/${javascript.src}" `,
    `mkdir "${root.teste}/${css.past}/${css.src}" `,
    `mkdir "${root.teste}/${css.past}/${css.src}/page" `,
    `mkdir "${root.teste}/${css.past}/${css.src}/page/common" `,
    // FILES
    `touch "${root.teste}/${javascript.past}/${javascript.src}/menu.js" `,
    `touch "${root.teste}/${css.past}/${css.src}/init.scss" `,
    `touch "${root.teste}/${css.past}/${css.src}/page/page.scss" `,
    `touch "${root.teste}/${css.past}/${css.src}/page/common/header.scss" `,
    `touch "${root.teste}/${css.past}/${css.src}/page/common/footer.scss" `,
    `touch "${root.teste}/index.html" `,
  ]
));





// DESENVOLVIMENTO

gulp.task('styles-min', () => {
  gulp.src(`${root.dev}/${files.past}/${css.past}/${css.src}/init.scss`)
    .pipe(concat(`${css.name_min}`))
    .pipe(sass({ outputStyle: 'compressed' }))
    // .pipe(autoprefixer({ browsers: ['last 2 versions'], cascade: false }))
    .pipe(gulp.dest(`${root.dev}/${files.past}/${css.past}`))
  console.log(`${css.name} OK`)
});

gulp.task('styles', () => {
  gulp.src(`${root.dev}/${files.past}/${css.past}/${css.src}/init.scss`)
    .pipe(concat(`${css.name}`))
    .pipe(sass())
    .pipe(gulp.dest(`${root.dev}/${files.past}/${css.past}`))
  console.log(`${css.name} OK`);
});

gulp.task('scripts-min', () => {
  gulp.src(`${root.dev}/${files.past}/${javascript.past}/${javascript.src}/${javascript.allFile}`)
    .pipe(concat(`${javascript.name_min}`))
    .pipe(babel({ presets: ['env'] }))
    .pipe(uglify())
    .pipe(gulp.dest(`${root.dev}/${files.past}/${javascript.past}`))
  console.log(`${javascript.name} OK`)
});

gulp.task('scripts', () => {
  gulp.src(`${root.dev}/${files.past}/${javascript.past}/${javascript.src}/${javascript.allFile}`)
    .pipe(concat(`${javascript.name}`))
    .pipe(gulp.dest(`${root.dev}/${files.past}/${javascript.past}`))
  console.log(`${javascript.name} OK`)
});

gulp.task('watch', () => {
  gulp.watch(`${root.dev}/${files.past}/${css.past}/${css.src}/${css.allFile}`, ['styles', 'styles-min']).on('change', browserSync.reload);
  gulp.watch(`${root.dev}/${files.past}/${javascript.past}/${javascript.src}/${javascript.allFile}`, ['scripts', 'scripts-min']).on('change', browserSync.reload);
  gulp.watch(`${root.dev}/${structure.allFile_php}`).on('change', browserSync.reload);
});

gulp.task('default', () => {
  // gulp.start('serve', 'styles', 'styles-min', 'scripts', 'scripts-min', 'watch');
  gulp.start('styles', 'styles-min', 'scripts', 'scripts-min', 'watch');
})





// DEPLOY

gulp.task('resetFolders', shell.task(
  [
    `rm -rf "${root.deploy}" `,
    `mkdir "${root.deploy}" `,
    `mkdir "${root.deploy}/${files.past}" `,
    `mkdir "${root.deploy}/${javascript.past}" `,
    `mkdir "${root.deploy}/${css.past}" `,
  ]
));

gulp.task('D-styles-min', () => {
  gulp.src(`${root.dev}/${css.past}/${css.src}/init.scss`)
    .pipe(concat(`${css.name_min}`))
    .pipe(sass({ outputStyle: 'compressed' }))
    .pipe(autoprefixer({ browsers: ['last 2 versions'], cascade: false }))
    .pipe(gulp.dest(`${root.deploy}/${css.past}`))
});

gulp.task('D-scripts-min', () => {
  gulp.src(`${root.dev}/${javascript.past}/${javascript.src}/${javascript.allFile}`)
    .pipe(concat(`${javascript.name_min}`))
    .pipe(babel({ presets: ['env'] }))
    .pipe(uglify())
    .pipe(gulp.dest(`${root.deploy}/${javascript.past}`))
});

gulp.task('copy-assets', () => {
  gulp.src(`${root.dev}/${files.past}/**/*`)
    .pipe(plumber())
    .pipe(gulp.dest(`${root.deploy}/${files.past}`))
})

gulp.task('copy-html', () => {
  gulp.src(`${root.dev}/*.${structure.tipe_html}`)
    .pipe(plumber())
    .pipe(gulp.dest(`${root.deploy}/`))
})

gulp.task('deploy', () => {
  gulp.run(gulpSequence('resetFolders', 'D-styles-min', 'D-scripts-min', 'copy-assets', 'copy-html'));
})