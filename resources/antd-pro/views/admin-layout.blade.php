@extends('antd::skeleton')

@section('container')
    <admin-layout title="@yield('title')">
        <template v-slot:head-tools>
            @stack('head-tools')
        </template>
        <template v-slot:content>
            @yield('content')
        </template>
        <template v-slot:footer>
            @yield('footer', View::make('antd::components.page-footer'))
        </template>
    </admin-layout>
@endsection
