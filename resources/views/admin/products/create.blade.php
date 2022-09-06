@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.product.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.products.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.product.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($product) ? $product->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                <label for="description">{{ trans('global.product.fields.description') }}</label>
                <textarea id="description" name="description" class="form-control ">{{ old('description', isset($product) ? $product->description : '') }}</textarea>
                @if($errors->has('description'))
                    <em class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.description_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                <label for="price">{{ trans('global.product.fields.price') }}</label>
                <input type="number" id="price" name="price" class="form-control" value="{{ old('price', isset($product) ? $product->price : '') }}" step="0.01">
                @if($errors->has('price'))
                    <em class="invalid-feedback">
                        {{ $errors->first('price') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.price_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('discount') ? 'has-error' : '' }}">
                <label for="discount">{{ trans('global.product.fields.discount') }}</label>
                <input type="number" id="discount" name="discount" class="form-control" value="{{ old('discount', isset($product) ? $product->discount : '') }}" step="0.01">
                @if($errors->has('discount'))
                    <em class="invalid-feedback">
                        {{ $errors->first('discount') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.discount_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('bv') ? 'has-error' : '' }}">
                <label for="bv">{{ trans('global.product.fields.bv') }}</label>
                <input type="number" id="bv" name="bv" class="form-control" value="{{ old('bv', isset($product) ? $product->bv : '') }}" step="0.01">
                @if($errors->has('bv'))
                    <em class="invalid-feedback">
                        {{ $errors->first('bv') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.bv_helper') }}
                </p>
            </div>
            <!-- <div class="form-group {{ $errors->has('cogs') ? 'has-error' : '' }}">
                <label for="cogs">{{ trans('global.product.fields.cogs') }}</label>
                <input type="number" id="cogs" name="cogs" class="form-control" value="{{ old('cogs', isset($product) ? $product->cogs : '') }}" step="0.01">
                @if($errors->has('cogs'))
                    <em class="invalid-feedback">
                        {{ $errors->first('cogs') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.product.fields.cogs_helper') }}
                </p>
            </div> -->
            <div class="card">
                <div class="card-header">
                    Biaya Produksi (Dana)
                </div>

                <div class="card-body">
                    <table class="table" id="accounts_table">
                        <thead>
                            <tr>
                                <th>Items</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (old('accounts', ['']) as $index => $oldAccount)
                                <tr id="account{{ $index }}">
                                    <td>
                                        <select name="accounts[]" class="form-control">
                                            <option value="">-- choose account --</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}"{{ $oldAccount == $account->id ? ' selected' : '' }}>
                                                    {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="amounts[]" class="form-control" value="{{ old('amounts.' . $index) ?? '1' }}" />
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="account{{ count(old('accounts', [''])) }}"></tr>
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