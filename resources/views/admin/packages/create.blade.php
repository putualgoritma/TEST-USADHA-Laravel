@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.package.title_singular') }}
    </div>

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-body">
        <form action="{{ route("admin.packages.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.package.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($package) ? $package->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.package.fields.description') }}</label>
                <textarea id="description" name="description" class="form-control ">{{ old('description', isset($package) ? $package->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.description_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('package_type') ? 'has-error' : '' }}">
                <label for="package_type">{{ trans('global.package.fields.package_type') }}*</label>
                <select name="package_type" class="form-control">
                    <option value="none"{{ old('package_type') == 'none' ? ' selected' : '' }}>None</option>
                    <option value="promo"{{ old('package_type') == 'promo' ? ' selected' : '' }}>Spesial Promo</option>
                    <option value="agent"{{ old('package_type') == 'agent' ? ' selected' : '' }}>Agent</option>
                    <option value="member"{{ old('package_type') == 'member' ? ' selected' : '' }}>Member</option>
                    <option value="reseller"{{ old('package_type') == 'reseller' ? ' selected' : '' }}>Reseller</option>
                    <option value="refill"{{ old('package_type') == 'refill' ? ' selected' : '' }}>Refill</option>
                    <option value="conventional"{{ old('package_type') == 'conventional' ? ' selected' : '' }}>Conventional</option>
                    <option value="upgrade"{{ old('package_type') == 'upgrade' ? ' selected' : '' }}>Upgrade</option>
                </select>
                @if($errors->has('package_type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('package_type') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.package_type_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('activation_type_id') ? 'has-error' : '' }}">
                <label for="activation_type_id">{{ trans('global.networkfee.fields.activation_type_id') }}*</label>
                <select name="activation_type_id" class="form-control">
                    <option value="0">-- choose activation --</option>
                    @foreach ($activations as $activation)
                        <option value="{{ $activation->id }}"{{ old('code') == $activation->id ? ' selected' : '' }}>
                        {{ $activation->code }}-{{ $activation->name }} {{ $activation->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('activation_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('activation_type_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.activation_type_id_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('upgrade_type_id') ? 'has-error' : '' }}">
                <label for="upgrade_type_id">{{ trans('global.networkfee.fields.upgrade_type_id') }}*</label>
                <select name="upgrade_type_id" class="form-control">
                    <option value="0">-- choose activation --</option>
                    @foreach ($activations as $activation)
                        <option value="{{ $activation->id }}"{{ old('code') == $activation->id ? ' selected' : '' }}>
                        {{ $activation->code }}-{{ $activation->name }} {{ $activation->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('upgrade_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('upgrade_type_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.upgrade_type_id_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                <label for="price">{{ trans('global.package.fields.price') }}</label>
                <input type="number" id="price" name="price" class="form-control" value="{{ old('price', isset($package) ? $package->price : '') }}" step="0.01">
                @if($errors->has('price'))
                    <em class="invalid-feedback">
                        {{ $errors->first('price') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.price_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('discount') ? 'has-error' : '' }}">
                <label for="discount">{{ trans('global.package.fields.discount') }}</label>
                <input type="number" id="discount" name="discount" class="form-control" value="{{ old('discount', isset($package) ? $package->discount : '') }}" step="0.01">
                @if($errors->has('discount'))
                    <em class="invalid-feedback">
                        {{ $errors->first('discount') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.discount_helper') }}
                </p>
            </div>
            <!-- <div class="form-group {{ $errors->has('cogs') ? 'has-error' : '' }}">
                <label for="cogs">{{ trans('global.package.fields.cogs') }}</label>
                <input type="number" id="cogs" name="cogs" class="form-control" value="{{ old('cogs', isset($package) ? $package->cogs : '') }}" step="0.01">
                @if($errors->has('cogs'))
                    <em class="invalid-feedback">
                        {{ $errors->first('cogs') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.cogs_helper') }}
                </p>
            </div> -->

            <div class="card">
                <div class="card-header">
                    Products
                </div>

                <div class="card-body">
                    <table class="table" id="products_table">
                        <thead>
                            <tr>
                                <th>Items</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (old('products', ['']) as $index => $oldProduct)
                                <tr id="product{{ $index }}">
                                    <td>
                                        <select name="products[]" class="form-control">
                                            <option value="">-- choose product --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"{{ $oldProduct == $product->id ? ' selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="quantities[]" class="form-control" value="{{ old('quantities.' . $index) ?? '1' }}" />
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="product{{ count(old('products', [''])) }}"></tr>
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-md-12">
                            <button id="add_row" class="btn btn-default pull-left">+ Add Row</button>
                            <button id='delete_row' class="pull-right btn btn-danger">- Delete Row</button>
                        </div>
                    </div>
                </div>
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
    let row_number = {{ count(old('products', [''])) }};
    $("#add_row").click(function(e){
      e.preventDefault();
      let new_row_number = row_number - 1;
      $('#product' + row_number).html($('#product' + new_row_number).html()).find('td:first-child');
      $('#products_table').append('<tr id="product' + (row_number + 1) + '"></tr>');
      row_number++;
    });

    $("#delete_row").click(function(e){
      e.preventDefault();
      if(row_number > 1){
        $("#product" + (row_number - 1)).html('');
        row_number--;
      }
    });
  });
</script>
@endsection
