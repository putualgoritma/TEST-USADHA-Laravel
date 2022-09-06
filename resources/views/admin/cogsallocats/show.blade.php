@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.cogsallocat.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.cogsallocat.fields.account_id') }}
                    </th>
                    <td>
                        {{ $cogsallocat->account_id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.cogsallocat.fields.allocation') }}
                    </th>
                    <td>
                        {!! $cogsallocat->allocation !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection