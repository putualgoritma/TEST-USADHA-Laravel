@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.accountlock.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.accountlock.fields.account_id') }}
                    </th>
                    <td>
                        {!! $accountlock->account_id !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.accountlock.fields.code') }}
                    </th>
                    <td>
                        {{ $accountlock->code }}
                    </td>
                </tr>
                
            </tbody>
        </table>
    </div>
</div>

@endsection