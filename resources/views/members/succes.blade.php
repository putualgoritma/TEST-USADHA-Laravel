@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
        <div class="card">
    <div class="card-header">
        {{ trans('global.register') }} {{ trans('global.member.title_singular') }}
    </div>
    <div class="alert alert-danger">Registrasi Member Berhasil. Tolong Cek Email Untuk Melihat Account Details.</div>
</div>
        </div>
    </div>
</div>
@endsection