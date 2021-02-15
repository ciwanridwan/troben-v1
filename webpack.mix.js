const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
  .vue()

mix.less('resources/antd-pro/theme/index.less', 'public/build/app.css')
//   .postCss('resources/css/app.css', 'public/css', [

  // ]);

mix.webpackConfig({
  stats: "minimal",
  module: {
    rules: [{
      test: /\.less$/,
      use: [
        {
          loader: "less-loader",
          options: {
            lessOptions: {
              modifyVars: {
                'primary-color': '#1DA57A',
                'link-color': '#1DA57A',
                'border-radius-base': '2px',
              },
              javascriptEnabled: true,
            }
          }
        }
      ]
    }]
  },
})

mix.inProduction()
  ? mix.version()
  : mix.sourceMaps()
