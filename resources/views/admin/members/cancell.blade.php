@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
    Setujui Pembatalan Aktivasi
    </div>

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-body">
        <form action="{{ route("admin.members.cancellprocess") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')   
                     
            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
            <div class="checkbox">
            <label>Setujui Pembatalan?</label>
            <input type="checkbox" data-toggle="toggle" name="status" id="status" data-on="Ya" data-off="Tidak">    
            </div>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
                <input type="hidden" id="id" name="id" value="{{ $member->id }}">
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="Proses Pembatalan">
            </div>
        </form>


    </div>
</div>
@endsection
