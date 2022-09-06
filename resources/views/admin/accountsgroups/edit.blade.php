@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.accountsgroup.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.accountsgroups.update", [$accountsgroup->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('accounts_type_id') ? 'has-error' : '' }}">
                <label for="accounts_type_id">{{ trans('global.accountsgroup.fields.accounts_type_id') }}*</label>
                <select name="accounts_type_id" id="accounts_type_id" class="form-control">
                <option value="">== Select Account ==</option>
                @foreach ($accountstype as $id => $name)
                    <option value="{{ $id }}" {{ $accountsgroup->accounts_type_id == $id ? 'selected="selected"' : '' }}>{{ $name }}</option>
                @endforeach
                </select>
                @if($errors->has('accounts_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('accounts_type_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.accountsgroup.fields.accounts_type_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.accountsgroup.fields.code') }}</label>
                <input type="text" id="code" name="code" class="form-control" maxlength="2" value="{{ old('code', isset($accountsgroup) ? $accountsgroup->code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.accountsgroup.fields.code_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.accountsgroup.fields.name') }}</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($accountsgroup) ? $accountsgroup->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.accountsgroup.fields.name_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection