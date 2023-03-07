@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.networkfee.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.fees.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.networkfee.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($networkfee) ? $networkfee->code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.code_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.networkfee.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($networkfee) ? $networkfee->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.name_helper') }}
                </p>
            </div>
            
            <div class="form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                <label for="amount">{{ trans('global.networkfee.fields.amount') }}</label>
                <input type="text" id="amount" name="amount" class="form-control" value="{{ old('amount', isset($networkfee) ? $networkfee->amount : '0') }}" step="1.00">
                @if($errors->has('amount'))
                    <em class="invalid-feedback">
                        {{ $errors->first('amount') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.amount_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="type">{{ trans('global.networkfee.fields.type') }}*</label>
                <select name="type" class="form-control">
                    <option value="none">-- choose type --</option>
                    <option value="activation">Aktivasi</option>  
                    <option value="ro">RO</option>
                    <option value="conventional">Konvensional</option>
                    <option value="pairing">Pairing</option>
                    <option value="matching">Matching</option>                  
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.type_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('deep_level') ? 'has-error' : '' }}">
                <label for="deep_level">{{ trans('global.networkfee.fields.deep_level') }}</label>
                <input type="number" id="deep_level" name="deep_level" class="form-control" value="{{ old('deep_level', isset($networkfee) ? $networkfee->deep_level : '0') }}" step="1">
                @if($errors->has('deep_level'))
                    <em class="invalid-feedback">
                        {{ $errors->first('deep_level') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.deep_level_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('fee_day_max') ? 'has-error' : '' }}">
                <label for="fee_day_max">{{ trans('global.networkfee.fields.fee_day_max') }}</label>
                <input type="number" id="fee_day_max" name="fee_day_max" class="form-control" value="{{ old('fee_day_max', isset($networkfee) ? $networkfee->fee_day_max : '0') }}" step="1">
                @if($errors->has('fee_day_max'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fee_day_max') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.fee_day_max_helper') }}
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

            <div class="form-group {{ $errors->has('sbv') ? 'has-error' : '' }}">
                <label for="sbv">{{ trans('global.networkfee.fields.sbv') }}</label>
                <input type="text" id="sbv" name="sbv" class="form-control" value="{{ old('sbv', isset($networkfee) ? $networkfee->sbv : '0') }}">
                @if($errors->has('sbv'))
                    <em class="invalid-feedback">
                        {{ $errors->first('sbv') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.sbv_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('sbv2') ? 'has-error' : '' }}">
                <label for="sbv2">{{ trans('global.networkfee.fields.sbv2') }}</label>
                <input type="text" id="sbv2" name="sbv2" class="form-control" value="{{ old('sbv2', isset($networkfee) ? $networkfee->sbv2 : '0') }}">
                @if($errors->has('sbv2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('sbv2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.sbv2_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('saving') ? 'has-error' : '' }}">
                <label for="saving">{{ trans('global.networkfee.fields.saving') }}*</label>
                <select name="saving" class="form-control">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                    
                </select>
                @if($errors->has('saving'))
                    <em class="invalid-feedback">
                        {{ $errors->first('saving') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.saving_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('rsbv_g1') ? 'has-error' : '' }}">
                <label for="rsbv_g1">{{ trans('global.networkfee.fields.rsbv_g1') }}</label>
                <input type="number" id="rsbv_g1" name="rsbv_g1" class="form-control" value="{{ old('rsbv_g1', isset($networkfee) ? $networkfee->rsbv_g1 : '0') }}" step="1.00">
                @if($errors->has('rsbv_g1'))
                    <em class="invalid-feedback">
                        {{ $errors->first('rsbv_g1') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.rsbv_g1_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('rsbv_g2') ? 'has-error' : '' }}">
                <label for="rsbv_g2">{{ trans('global.networkfee.fields.rsbv_g2') }}</label>
                <input type="number" id="rsbv_g2" name="rsbv_g2" class="form-control" value="{{ old('rsbv_g2', isset($networkfee) ? $networkfee->rsbv_g2 : '0') }}" step="1.00">
                @if($errors->has('rsbv_g2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('rsbv_g2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.rsbv_g2_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('bv_min_pairing') ? 'has-error' : '' }}">
                <label for="bv_min_pairing">{{ trans('global.networkfee.fields.bv_min_pairing') }}</label>
                <input type="number" id="bv_min_pairing" name="bv_min_pairing" class="form-control" value="{{ old('bv_min_pairing', isset($networkfee) ? $networkfee->bv_min_pairing : '0') }}" step="1.00">
                @if($errors->has('bv_min_pairing'))
                    <em class="invalid-feedback">
                        {{ $errors->first('bv_min_pairing') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.bv_min_pairing_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('cba2') ? 'has-error' : '' }}">
                <label for="cba2">{{ trans('global.networkfee.fields.cba2') }}*</label>
                <select name="cba2" class="form-control">                    
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                    
                </select>
                @if($errors->has('cba2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('cba2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.cba2_helper') }}
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
