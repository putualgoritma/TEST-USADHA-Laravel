@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.asset.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.asset.fields.name') }}
                    </th>
                    <td>
                        {{ $asset->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.asset.fields.description') }}
                    </th>
                    <td>
                        {!! $asset->description !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.asset.fields.value') }}
                    </th>
                    <td>
                        Rp. {{ $asset->value }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection