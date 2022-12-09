@extends('antd::trawl-admin-layout')

@section('content')
    <form method="GET">

        <input type="text" id="source" name="search">
        <button type="submit">Search!</button>
    </form>

    <hr>

    <table border="1">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Name</th>
                <th>Partner</th>
                <th>Roles</th>
                <th>Is Deleted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr>
                <td>{{$u->username}}</td>
                <td>{{$u->email}}</td>
                <td>{{$u->name}}</td>
                <td>{{$u->partner}}</td>
                <td>{{$u->roles}}</td>
                <td>{{$u->deleted_at}}</td>
                <td>
                    @if(is_null($u->deleted_at))
                    <form method="POST">
                        {{csrf_field()}}

                        <input type="hidden" name="email" value="{{$u->email}}">
                        <button type="submit">LOGIN!</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('js_after')
<script>
    console.log(window.axios)
</script>
@endsection