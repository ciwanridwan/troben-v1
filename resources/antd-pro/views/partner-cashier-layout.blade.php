@extends('antd::skeleton')

@section('container')
    <trawl-cashier-layout title="@yield('title')">
        <template slot="head-tools">
            @stack('head-tools')
        </template>
        <template slot="content">
            @yield('content')
        </template>
        <template slot="footer">
            @yield('footer', View::make('antd::components.page-footer'))
        </template>
    </trawl-cashier-layout>
@endsection
