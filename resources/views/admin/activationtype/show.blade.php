@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.activation_type.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.activation_type.fields.name') }}
                    </th>
                    <td>
                        {{ $activation_type->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.activation_type.fields.type') }}
                    </th>
                    <td>
                        {!! $activation_type->type !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.activation_type.fields.bv_min') }}
                    </th>
                    <td>
                        {{ $activation_type->bv_min }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.activation_type.fields.bv_max') }}
                    </th>
                    <td>
                        {{ $activation_type->bv_max }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection