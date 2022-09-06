@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.account.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.account.fields.name') }}
                    </th>
                    <td>
                        {{ $account->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.account.fields.code') }}
                    </th>
                    <td>
                        {!! $account->code !!}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection