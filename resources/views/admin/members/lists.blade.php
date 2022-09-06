@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.member.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.member.fields.name') }}
                    </th>
                    <td>
                        {{ $member->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.member.fields.address') }}
                    </th>
                    <td>
                        {!! $member->address !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.member.fields.email') }}
                    </th>
                    <td>
                        ${{ $member->email }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection