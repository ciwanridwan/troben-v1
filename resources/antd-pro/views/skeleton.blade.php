<!doctype html>
<html lang="{{ config('app.locale') }}" class="no-focus">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>@yield('title', 'Home') - {{ config('app.name') }}</title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @yield('css_before')
  <link rel="stylesheet" href="{{ mix('/build/app.bundled.css') }}">
  @yield('css_after')
</head>
<body>
<noscript>
  <strong>We're sorry but {{ config('app.name') }} doesn't work properly without JavaScript enabled. Please enable it to
    continue.</strong>
</noscript>
<div id="app">
  @yield('container')
</div>

@yield('js_before')
<script type="text/javascript">window.Laravel = @json($laravelJs);</script>
<script type="text/javascript" src="{{ mix('/build/app.bundled.js') }}"></script>
@yield('js_after')
</body>
</html>
