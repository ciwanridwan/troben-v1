@extends('antd::skeleton')

@section('container')
  <admin-layout sidebar>
    <template v-slot:sidebar-menu>
      @yield('sidebar')
    </template>
    <template v-slot:content>
      @yield('content')
    </template>
    <template v-slot:footer>
      @yield('footer', View::make('antd::components.page-footer'))
    </template>
  </admin-layout>
@endsection
