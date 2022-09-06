@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.asset.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.assets.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="register">{{ trans('global.order.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($order) ? $order->register : date('Y-m-d')) }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.register_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.order.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($order) ? $order->code : $code) }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.code_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.asset.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($asset) ? $asset->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.asset.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('qty') ? 'has-error' : '' }}">
                <label for="qty">{{ trans('global.asset.fields.qty') }}*</label>
                <input type="number" id="qty" name="qty" class="form-control" value="{{ old('qty', isset($asset) ? $asset->qty : '') }}">
                @if($errors->has('qty'))
                    <em class="invalid-feedback">
                        {{ $errors->first('qty') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.asset.fields.qty_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('depreciation_type') ? 'has-error' : '' }}">
                <label for="depreciation_type">{{ trans('global.asset.fields.depreciation_type') }}*</label>
                <select name="depreciation_type" class="form-control">
                    <option value="straight_line">Garis Lurus</option>                    
                </select>
                @if($errors->has('depreciation_type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('depreciation_type') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.asset.fields.depreciation_type_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('value') ? 'has-error' : '' }}">
                <label for="value">{{ trans('global.asset.fields.value') }}</label>
                <input type="number" id="value" name="value" class="form-control" value="{{ old('value', isset($asset) ? $asset->value : '') }}" step="0.01">
                @if($errors->has('value'))
                    <em class="invalid-feedback">
                        {{ $errors->first('value') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.asset.fields.value_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('life_period') ? 'has-error' : '' }}">
                <label for="life_period">{{ trans('global.asset.fields.life_period') }} (bulan)</label>
                <input type="number" id="life_period" name="life_period" class="form-control" value="{{ old('life_period', isset($asset) ? $asset->life_period : '') }}" step="0.01">
                @if($errors->has('life_period'))
                    <em class="invalid-feedback">
                        {{ $errors->first('life_period') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.asset.fields.life_period_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('accounts_id') ? 'has-error' : '' }}">
                <label for="accounts_id">{{ trans('global.order.fields.accounts_id') }}*</label>
                <select name="accounts_id" class="form-control">
                    <option value="">-- choose account --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}"{{ old('code') == $account->id ? ' selected' : '' }}>
                        {{ $account->code }}-{{ $account->name }} {{ $account->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('accounts_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('accounts_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.order.fields.accounts_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.asset.fields.description') }}</label>
                <textarea id="description" name="description" class="form-control ">{{ old('description', isset($asset) ? $asset->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.asset.fields.description_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
  $(document).ready(function(){
    let row_number = {{ count(old('accounts', [''])) }};
    $("#add_row").click(function(e){
      e.preventDefault();
      let new_row_number = row_number - 1;
      $('#account' + row_number).html($('#account' + new_row_number).html()).find('td:first-child');
      $('#accounts_table').append('<tr id="account' + (row_number + 1) + '"></tr>');
      row_number++;
    });

    $("#delete_row").click(function(e){
      e.preventDefault();
      if(row_number > 1){
        $("#account" + (row_number - 1)).html('');
        row_number--;
      }
    });
  });
</script>
@endsection