@extends('antd::skeleton')

@section('container')
    <trawl-admin-layout title="@yield('title')">
        <template slot="head-tools">
            @stack('head-tools')
        </template>
        <template slot="content">
            <h3
                class="trawl-text-bolder trawl-text-danger"
                style="margin: 50px"
                >
Pengumuman Penting <br>                
Mulai tanggal 1 Agustus 2023, dashboard lama akan dimatikan. <br>
Mohon gunakan dashboard baru di alamat <a href="https://admin.trawlbens.com/">admin.trawlbens.com</a> sebagai penggantinya. 
                </h3>
            @yield('content')
        </template>
        <template slot="footer">
            @yield('footer', View::make('antd::components.page-footer'))
        </template>
    </trawl-admin-layout>
@endsection
