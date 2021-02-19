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

mix.less('resources/antd-pro/theme/index.less', 'public/css/antd.css')

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
                'primary-color': '#E50013',
                'link-color': '#E50013',
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

mix
  .disableSuccessNotifications()
  .version()

if (mix.inProduction()) {
  mix.sourceMaps()
}
