@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.agent.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.agent.fields.name') }}
                    </th>
                    <td>
                        {{ $agent->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.agent.fields.address') }}
                    </th>
                    <td>
                        {!! $agent->address !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.agent.fields.phone') }}
                    </th>
                    <td>
                        {{ $agent->phone }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection