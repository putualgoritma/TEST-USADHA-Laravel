@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.activation_type.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.activation-type.update", [$activation_type->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.activation_type.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($activation_type) ? $activation_type->name : '') }}" readonly>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.activation_type.fields.name_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="type">{{ trans('global.activation_type.fields.type') }}*</label>
                <select name="type" class="form-control" readonly>
                    <option value="user"{{(old('type', $activation_type->type) == 'user' ? 'selected' : '')}}>user</option>  
                    <option value="business"{{(old('type', $activation_type->type) == 'business' ? 'selected' : '')}}>business</option>                  
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </em>
                @endif
                <p class="helper-block">
               {{ trans('global.activation_type.fields.type_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('bv_min') ? 'has-error' : '' }}">
                <label for="bv_min">{{ trans('global.activation_type.fields.bv_min') }}*</label>
                <input type="text" id="bv_min" name="bv_min" class="form-control" value="{{ old('bv_min', isset($activation_type) ? $activation_type->bv_min : '') }}">
                @if($errors->has('bv_min'))
                    <em class="invalid-feedback">
                        {{ $errors->first('bv_min') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.activation_type.fields.bv_min_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('bv_max') ? 'has-error' : '' }}">
                <label for="bv_max">{{ trans('global.activation_type.fields.bv_max') }}</label>
                <input type="text" id="bv_max" name="bv_max" class="form-control" value="{{ old('bv_max', isset($activation_type) ? $activation_type->bv_max : '') }}" step="0.01">
                @if($errors->has('bv_max'))
                    <em class="invalid-feedback">
                        {{ $errors->first('bv_max') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.activation_type.fields.bv_max_helper') }}
                </p>
            </div>
            
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection