@extends('layouts.admin')
@section('content')

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="card">
    <div class="card-header">
        Upgrade {{ trans('global.member.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.members.upgradeprocess", [$member->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')           

            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.member.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($member) ? $member->code : '') }}" readonly>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.code_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.member.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($member) ? $member->name : '') }}" readonly>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('activation_type_id') ? 'has-error' : '' }}">
                <label for="activation_type_id">Tipe*</label>
                <select name="activation_type_id" class="form-control">
                    <option value="">-- pilih tipe --</option>
                    @foreach ($activationtypes as $activationtype)
                        <option value="{{ $activationtype->id }}"{{(old('activation_type_id', $member->ref_id) == $activationtype->id ? 'selected' : '')}}>
                        {{ $activationtype->code }}-{{ $activationtype->name }} {{ $activationtype->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('activation_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('activation_type_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            
            <div>
            <input type="hidden" name="id_hidden" value="{{$member->id}}">    
            <input class="btn btn-danger" type="submit" value="Upgrade">
            </div>
        </form>
    </div>
</div>

@endsection