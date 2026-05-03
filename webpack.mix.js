let mix = require('laravel-mix')
let NovaExtension = require('laravel-nova-devtool')

mix.extend('nova', new NovaExtension())

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .vue({ version: 3 })
  .css('resources/css/field.css', 'css')
  .nova('opscale-co/nova-geospatial-fields')
  .version()

// Nova only publishes the bundle's JS and CSS — image assets at
// dist/images/* are never served, so any URL Leaflet generates for
// its marker/sprite PNGs 404s. Force webpack to inline every PNG and
// SVG as a data URI so the bundle is self-contained.
mix.override((config) => {
  config.module.rules.forEach((rule) => {
    if (!rule.test) return
    const test = rule.test.toString()
    const isImageRule =
      test.includes('png') ||
      test.includes('jpe') ||
      test.includes('gif') ||
      test.includes('webp') ||
      (test.includes('svg') && !test.includes('font'))
    if (!isImageRule) return
    rule.type = 'asset/inline'
    delete rule.use
    delete rule.loader
    delete rule.generator
    delete rule.parser
  })
})
