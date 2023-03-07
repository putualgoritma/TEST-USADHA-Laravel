@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Inisiasi {{ trans('global.career.title_singular') }}
    </div>

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-body">
        <form action="{{ route("admin.careers.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('customer_id') ? 'has-error' : '' }}">
                <label for="customer_id">Pilih member*

                </label>
                <div class="row">
                    <div class="col-md-9">
                     <select name="customer_id" id="customer_id" class="form-control select">
                         <option value="{{$customer_selected->id}}">
                         {{$customer_selected->code}}-{{$customer_selected->name}}
                        </option>                        
                </select>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.careers.listMember') }}" class="btn btn-warning">Pilih Member</a>
            </div>
            </div>          
            
            </div>

            <div class="form-group {{ $errors->has('careertype_id') ? 'has-error' : '' }}">
                <label for="careertype_id">{{ trans('global.career.fields.careertype_id') }}*</label>
                <select name="careertype_id" class="form-control">
                    <option value="">-- choose jenjang --</option>
                    @foreach ($careertypes as $careertype)
                        <option value="{{ $careertype->id }}"{{ old('careertype_id') == $careertype->id ? ' selected' : '' }}>
                        {{ $careertype->name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('careertype_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('careertype_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.career.fields.careertype_id_helper') }}
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
