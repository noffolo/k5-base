const purgecss = require('@fullhuman/postcss-purgecss');
const glob = require('glob-all');
const path = require('path');

module.exports = {
    plugins: [
        process.env.NODE_ENV === 'production' ? purgecss({
            content: glob.sync([
                path.join(__dirname, 'site/snippets/**/*.php'),
                path.join(__dirname, 'site/templates/**/*.php'),
                path.join(__dirname, 'assets/src/js/**/*.js'),
                path.join(__dirname, 'assets/src/sass/**/*.scss'),
                path.join(__dirname, 'content/**/*.txt'),
            ]),
            defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],
            safelist: {
                standard: [
                    /^swiper-/,
                    /^leaflet-/,
                    /^navbar-/,
                    /^nav-/,
                    /^collapse/,
                    /^show/,
                    /^active/,
                    'dragging',
                    'no-scroll'
                ],
                deep: [/^swiper-/, /^leaflet-/],
                greedy: [/^swiper-/, /^leaflet-/]
            }
        }) : null,
        require('autoprefixer'),
    ],
};
