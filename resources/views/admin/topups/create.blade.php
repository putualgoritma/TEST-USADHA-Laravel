@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.topup.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.topups.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="register">{{ trans('global.topup.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($topup) ? $topup->register : date('Y-m-d')) }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.topup.fields.register_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.topup.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($topup) ? $topup->code : $code) }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.topup.fields.code_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('customers_id') ? 'has-error' : '' }}">
                <label for="customers_id">{{ trans('global.topup.fields.customers_id') }}*</label>
                <select name="customers_id" class="form-control">
                    <option value="">-- choose customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"{{ old('code') == $customer->id ? ' selected' : '' }}>
                        {{ $customer->code }}-{{ $customer->name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('customers_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customers_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.topup.fields.customers_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.topup.fields.memo') }}</label>
                <textarea id="memo" name="memo" class="form-control ">{{ old('memo', isset($point) ? $point->memo : '') }}</textarea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.topup.fields.memo_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('accounts_id') ? 'has-error' : '' }}">
                <label for="accounts_id">{{ trans('global.topup.fields.accounts_id') }}*</label>
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
                    {{ trans('global.topup.fields.accounts_id_helper') }}
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    Points
                </div>

                <div class="card-body">
                    <table class="table" id="points_table">
                        <thead>
                            <tr>
                                <th>Points</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (old('points', ['']) as $index => $oldProduct)
                                <tr id="point{{ $index }}">
                                    <td>
                                        <select name="points[]" class="form-control point_list">
                                            <option value="">-- choose point --</option>
                                            @foreach ($points as $point)
                                                <option value="{{ $point->id }}"{{ $oldProduct == $point->id ? ' selected' : '' }}>
                                                    {{ $point->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="amounts[]" class="form-control" value="{{ old('amounts.' . $index) ?? '0' }}" />
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="point{{ count(old('points', [''])) }}"></tr>
                        </tbody>
                        <tr>
                                    <td>
                                    Total
                                    </td>
                                    <td>
                                    <input type="number" name="total" class="form-control" value="{{ old('total') ?? '0' }}" readonly />
                                    </td>
                                </tr>
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
    let row_number = {{ count(old('points', [''])) }};
    $("#add_row").click(function(e){
      e.preventDefault();
      let new_row_number = row_number - 1;
      $('#point' + row_number).html($('#point' + new_row_number).html()).find('td:first-child');
      $('#points_table').append('<tr id="point' + (row_number + 1) + '"></tr>');
      row_number++;
    });

    $("#delete_row").click(function(e){
      e.preventDefault();
      if(row_number > 1){
        $("#point" + (row_number - 1)).html('');
        row_number--;
      }
    });    
  });
</script>
@endsection
