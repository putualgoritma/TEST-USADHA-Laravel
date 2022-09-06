@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.capitalist.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.capitalist.fields.name') }}
                    </th>
                    <td>
                        {{ $capitalist->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.capitalist.fields.address') }}
                    </th>
                    <td>
                        {!! $capitalist->address !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.capitalist.fields.phone') }}
                    </th>
                    <td>
                        {{ $capitalist->phone }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection