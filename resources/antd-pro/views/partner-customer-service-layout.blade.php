@extends('antd::skeleton')

@section('container')
    <trawl-customer-service-layout title="@yield('title')">
        <template slot="head-tools">
            @stack('head-tools')
        </template>
        <template slot="content">
            @yield('content')
        </template>
        <template slot="footer">
            @yield('footer', View::make('antd::components.page-footer'))
        </template>
    </trawl-customer-service-layout>
@endsection
