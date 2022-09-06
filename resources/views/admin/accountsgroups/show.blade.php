@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.accountsgroup.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.accountsgroup.fields.accounts_type_id') }}
                    </th>
                    <td>
                        {{ $accountsgroup->accounts_type_id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.accountsgroup.fields.code') }}
                    </th>
                    <td>
                        {!! $accountsgroup->code !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.accountsgroup.fields.name') }}
                    </th>
                    <td>
                        {!! $accountsgroup->name !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection