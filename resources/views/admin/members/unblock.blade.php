@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Approved {{ trans('global.member.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.members.unblockprocess") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')   
                     
            <div class="form-group {{ $errors->has('status_block') ? 'has-error' : '' }}">
            <div class="checkbox">
            <label>Buka {{ trans('global.member.fields.status_block') }}*</label>
            <input type="checkbox" data-toggle="toggle" name="status_block" id="status_block">    
            </div>
                @if($errors->has('status_block'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status_block') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.status_block_helper') }}
                </p>
                <input type="hidden" id="id" name="id" value="{{ $member->id }}">
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="Unblock">
            </div>
        </form>


    </div>
</div>
@endsection
