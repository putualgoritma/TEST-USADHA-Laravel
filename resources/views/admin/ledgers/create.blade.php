@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.ledger.title_singular') }}
    </div>

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-body">
        <form action="{{ route("admin.ledgers.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="register">{{ trans('global.ledger.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($ledger) ? $ledger->register : '') }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.ledger.fields.register_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">{{ trans('global.ledger.fields.title') }}*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($ledger) ? $ledger->title : '') }}" required>
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.ledger.fields.title_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.ledger.fields.memo') }}</label>
                <textarea id="memo" name="memo" class="form-control ">{{ old('memo', isset($product) ? $product->memo : '') }}</textarea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.ledger.fields.memo_helper') }}
                </p>
            </div>

            <div class="card">
                <div class="card-header">
                    Accounts
                </div>

                <div class="card-body">
                    <table class="table" id="accounts_table">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Type</th>
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
                                        <select name="types[]" class="form-control">
                                            <option value="">-- choose type --</option>
                                                <option value="D"{{ old('types.' . $index) == 'D' ? ' selected' : '' }}>
                                                    Debit
                                                </option>
                                                <option value="C"{{ old('types.' . $index) == 'C' ? ' selected' : '' }}>
                                                    Credit
                                                </option>
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
