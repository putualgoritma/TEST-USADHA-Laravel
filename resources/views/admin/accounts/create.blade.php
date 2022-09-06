@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.account.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.accounts.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('accounts_group_id') ? 'has-error' : '' }}">
                <label for="accounts_group_id">{{ trans('global.account.fields.accounts_group_id') }}*</label>
                <select name="accounts_group_id" id="accounts_group_id" class="form-control">
                <option value="">== Select Account ==</option>
                @foreach ($accountsgroup as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
                </select>
                @if($errors->has('accounts_group_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('accounts_group_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.account.fields.accounts_group_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.account.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($account) ? $account->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.account.fields.name_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection