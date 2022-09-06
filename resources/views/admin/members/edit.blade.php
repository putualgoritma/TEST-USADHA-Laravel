@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.member.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.members.update", [$member->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('register') ? 'has-error' : '' }}">
                <label for="register">{{ trans('global.member.fields.register') }}*</label>
                <input type="date" id="register" name="register" class="form-control" value="{{ old('register', isset($member) ? $member->register : '') }}" required>
                @if($errors->has('register'))
                    <em class="invalid-feedback">
                        {{ $errors->first('register') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.register_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.member.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($member) ? $member->code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.code_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('customers_id') ? 'has-error' : '' }}">
                <label for="customers_id">{{ trans('global.member.fields.customers_id') }}*</label>
                <select name="customers_id" class="form-control">
                    <option value="">-- choose customer --</option>
                    @foreach ($referals as $customer)
                        <option value="{{ $customer->id }}"{{(old('customers_id', $member->ref_id) == $customer->id ? 'selected' : '')}}>
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
                    {{ trans('global.member.fields.customers_id_helper') }}
                </p>
            </div>

            <!-- <div class="form-group {{ $errors->has('package_id') ? 'has-error' : '' }}">
                <label for="package_id">{{ trans('global.member.fields.package_id') }}*</label>
                <select name="package_id" class="form-control">
                    <option value="">-- choose paket --</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}"{{(old('package_id', $member->package_id) == $product->id ? 'selected' : '')}}>
                        {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('package_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('package_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.package_id_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('agents_id') ? 'has-error' : '' }}">
                <label for="agents_id">{{ trans('global.member.fields.agents_id') }}*</label>
                <select name="agents_id" class="form-control">
                    <option value="">-- choose agent --</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}"{{(old('agents_id', $member->agents_id) == $agent->id ? 'selected' : '')}}>
                        {{ $agent->code }}-{{ $agent->name }} {{ $agent->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('agents_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('agents_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.agents_id_helper') }}
                </p>
            </div> -->

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.member.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($member) ? $member->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">
                <label for="last_name">{{ trans('global.member.fields.last_name') }}</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', isset($member) ? $member->last_name : '') }}" step="0.01">
                @if($errors->has('last_name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('last_name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.last_name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="address">{{ trans('global.member.fields.address') }}</label>
                <textarea id="address" name="address" class="form-control ">{{ old('address', isset($member) ? $member->address : '') }}</textarea>
                @if($errors->has('address'))
                    <em class="invalid-feedback">
                        {{ $errors->first('address') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.address_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
                <label for="phone">{{ trans('global.member.fields.phone') }}</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', isset($member) ? $member->phone : '') }}" step="0.01">
                @if($errors->has('phone'))
                    <em class="invalid-feedback">
                        {{ $errors->first('phone') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.phone_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">{{ trans('global.member.fields.email') }}</label>
                <input type="text" id="email" name="email" class="form-control" value="{{ old('email', isset($member) ? $member->email : '') }}" step="0.01">
                @if($errors->has('email'))
                    <em class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.member.fields.email_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection