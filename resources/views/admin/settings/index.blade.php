@extends('layouts.admin')
@section('content')

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
    Update {{ trans('global.setting.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.settings.update") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')   
                     
            <div class="form-group {{ $errors->has('admin_acc_trsf') ? 'has-error' : '' }}">
            <div class="checkbox">
            <label>{{ trans('global.setting.fields.admin_acc_trsf') }}</label>
            <input type="checkbox" data-toggle="toggle" name="admin_acc_trsf" id="admin_acc_trsf" data-on="Ya" data-off="Tidak" {{ $env_arr['admin_acc_trsf'] == '1' ? 'checked' : '' }}>    
            </div>
                @if($errors->has('admin_acc_trsf'))
                    <em class="invalid-feedback">
                        {{ $errors->first('admin_acc_trsf') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.setting.fields.admin_acc_trsf_helper') }}
                </p>
                
            </div>
            <div class="form-group {{ $errors->has('member_activ') ? 'has-error' : '' }}">
            <div class="checkbox">
            <label>{{ trans('global.setting.fields.member_activ') }}</label>
            <input type="checkbox" data-toggle="toggle" name="member_activ" id="member_activ" data-on="Ya" data-off="Tidak" {{ $env_arr['member_activ'] == '1' ? 'checked' : '' }}>    
            </div>
                @if($errors->has('member_activ'))
                    <em class="invalid-feedback">
                        {{ $errors->first('member_activ') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.setting.fields.member_activ_helper') }}
                </p>
                
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="Proses">
            </div>
        </form>


    </div>
</div>
@endsection
