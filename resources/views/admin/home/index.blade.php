@extends('antd::admin-layout')

@section('title')
    Home
@endsection

@push('head-tools')
    <a-col :md="12">ABC</a-col>
@endpush

@section('content')
    <all-order></all-order>
@endsection
