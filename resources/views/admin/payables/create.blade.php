@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.payable.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.payables.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="register">{{ trans('global.payable.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($payable) ? $payable->register : date('Y-m-d')) }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.payable.fields.register_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.payable.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($payable) ? $payable->code : $code) }}" required>
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.payable.fields.code_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('customer_id') ? 'has-error' : '' }}">
                <label for="customer_id">{{ trans('global.payable.fields.customer_id') }}*</label>
                <select name="customer_id" class="form-control">
                    <option value="">-- choose customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"{{ old('code') == $customer->id ? ' selected' : '' }}>
                        {{ $customer->code }}-{{ $customer->name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('customer_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('customer_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.payable.fields.customer_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                <label for="amount">{{ trans('global.payable.fields.amount') }}*</label>
                <input type="number" id="amount" name="amount" class="form-control" value="{{ old('amount', isset($payable) ? $payable->amount : '') }}">
                @if($errors->has('amount'))
                    <em class="invalid-feedback">
                        {{ $errors->first('amount') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.payable.fields.amount_helper') }}
                </p>
            </div>            
            <div class="form-group {{ $errors->has('account_id') ? 'has-error' : '' }}">
                <label for="account_id">{{ trans('global.payable.fields.account_id') }}*</label>
                <select name="account_id" class="form-control">
                    <option value="">-- choose account --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}"{{ old('code') == $account->id ? ' selected' : '' }}>
                        {{ $account->code }}-{{ $account->name }} {{ $account->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('account_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('account_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.payable.fields.account_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('account_pay') ? 'has-error' : '' }}">
                <label for="account_pay">{{ trans('global.payable.fields.account_pay') }}*</label>
                <select name="account_pay" class="form-control">
                    <option value="">-- choose account --</option>
                    @foreach ($accounts_pay as $account)
                        <option value="{{ $account->id }}"{{ old('code') == $account->id ? ' selected' : '' }}>
                        {{ $account->code }}-{{ $account->name }} {{ $account->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('account_pay'))
                    <em class="invalid-feedback">
                        {{ $errors->first('account_pay') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.payable.fields.account_pay_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('memo') ? 'has-error' : '' }}">
                <label for="memo">{{ trans('global.payable.fields.memo') }}</label>
                <textarea id="memo" name="memo" class="form-control ">{{ old('memo', isset($payable) ? $payable->memo : '') }}</textarea>
                @if($errors->has('memo'))
                    <em class="invalid-feedback">
                        {{ $errors->first('memo') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.payable.fields.memo_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
