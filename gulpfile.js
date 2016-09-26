const elixir = require('laravel-elixir');

// browserify
require('laravel-elixir-browserify-official');
require('laravel-elixir-vueify');

// webpack
// require('laravel-elixir-vue');

elixir(mix => {
    mix.sass('app.scss')
        .browserify('app.js');
});
