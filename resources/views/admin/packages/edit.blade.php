@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.package.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.packages.update", [$package->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
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
            <div class="form-group {{ $errors->has('img') ? 'has-error' : '' }}">
                <label for="img">{{ trans('global.package.fields.img') }}*</label>
                <input type="file" id="img" name="img" class="form-control" value="{{ old('img', isset($package) ? $package->img : '') }}">
                @if($errors->has('img'))
                    <em class="invalid-feedback">
                        {{ $errors->first('img') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.img_helper') }}
                </p>
                <p>
                    <img src="{{ old('img', isset($package) ? $package->img : '') }}" alt="{{ old('name', isset($package) ? $package->name : '') }}" width="300">
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
                    <option value="none"{{ $package->package_type == 'none' ? 'selected="selected"' : '' }}>None</option>
                    <option value="promo"{{ $package->package_type == 'promo' ? 'selected="selected"' : '' }}>Spesial Promo</option>
                    <option value="agent"{{ $package->package_type == 'agent' ? 'selected="selected"' : '' }}>Agent</option>
                    <option value="member"{{ $package->package_type == 'member' ? 'selected="selected"' : '' }}>Member</option>
                    <option value="reseller"{{ $package->package_type == 'reseller' ? 'selected="selected"' : '' }}>Reseller</option>
                    <option value="refill"{{ $package->package_type == 'refill' ? 'selected="selected"' : '' }}>Refill</option>
                    <option value="conventional"{{ $package->package_type == 'conventional' ? 'selected="selected"' : '' }}>Conventional</option>
                    <option value="upgrade"{{ $package->package_type == 'upgrade' ? 'selected="selected"' : '' }}>Upgrade</option>
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
                        <option value="{{ $activation->id }}"{{ $package->activation_type_id == $activation->id ? ' selected' : '' }}>
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
                        <option value="{{ $activation->id }}"{{ $package->upgrade_type_id == $activation->id ? ' selected' : '' }}>
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

            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                <label for="status">{{ trans('global.package.fields.status') }}*</label>
                <select name="status" class="form-control">
                    <option value="show"{{ $package->status == 'show' ? 'selected="selected"' : '' }}>Show</option>
                    <option value="hidden"{{ $package->status == 'hidden' ? 'selected="selected"' : '' }}>Hide</option>                    
                </select>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.package.fields.status_helper') }}
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
                            <th>Product</th>
                            <th>Quantity</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach (old('products', $package->products->count() ? $package->products : ['']) as $package_product)
                            <tr id="product{{ $loop->index }}">
                                <td>
                                    <select name="products[]" class="form-control">
                                        <option value="">-- choose product --</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                @if (old('products.' . $loop->parent->index, optional($package_product)->id) == $product->id) selected @endif
                                            >{{ $product->name }} (Rp.{{ number_format($product->price, 2) }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="quantities[]" class="form-control"
                                           value="{{ (old('quantities.' . $loop->index) ?? optional(optional($package_product)->pivot)->quantity) ?? '1' }}" />
                                </td>
                            </tr>
                        @endforeach
                        <tr id="product{{ count(old('products', $package->products->count() ? $package->products : [''])) }}"></tr>
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
        let row_number = {{ count(old('products', $package->products->count() ? $package->products : [''])) }};
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
