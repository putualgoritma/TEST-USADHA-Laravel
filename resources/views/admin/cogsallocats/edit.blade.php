@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.cogsallocat.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.cogsallocats.update", [$cogsallocat->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- <div class="form-group {{ $errors->has('account_id') ? 'has-error' : '' }}">
                <label for="account_id">{{ trans('global.cogsallocat.fields.account_id') }}*</label>
                <input type="text" id="account_id" name="account_id" class="form-control" value="{{ old('account_id', isset($cogsallocat) ? $cogsallocat->account_id : '') }}">
                @if($errors->has('account_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('account_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.cogsallocat.fields.account_id_helper') }}
                </p>
            </div> -->
            <div class="form-group {{ $errors->has('account_id') ? 'has-error' : '' }}">
                <label for="account_id">{{ trans('global.cogsallocat.fields.account_id') }}*</label>
                <select name="account_id" id="account_id" class="form-control">
                <option value="">== Select Account ==</option>
                @foreach ($accounts as $id => $name)
                    <option value="{{ $id }}" {{ $cogsallocat->account_id == $id ? 'selected="selected"' : '' }}>{{ $name }}</option>
                @endforeach
                </select>
                @if($errors->has('account_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('account_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.cogsallocat.fields.account_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('allocation') ? 'has-error' : '' }}">
                <label for="allocation">{{ trans('global.cogsallocat.fields.allocation') }}</label>
                <input type="text" id="allocation" name="allocation" class="form-control" value="{{ old('allocation', isset($cogsallocat) ? $cogsallocat->allocation : '') }}">
                @if($errors->has('allocation'))
                    <em class="invalid-feedback">
                        {{ $errors->first('allocation') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.cogsallocat.fields.allocation_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection