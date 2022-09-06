@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.martregister.title') }}
    </div>

    <div class="card-body">
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('global.martregister.fields.id') }}
                        </th>
                        <td>
                            {{ $martregister->id }}
                        </td>
                    </tr>
                    @if($martregister->type!='registerdownline')
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.customer_id') }}
                        </th>
                        <td>
                        {{ $martregister->customers->code ?? '' }} - {{ $martregister->customers->name ?? '' }}
                        </td>
                    </tr>
                    @endif
                    @if($martregister->type=='registerdownline')
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.referal_id') }}
                        </th>
                        <td>
                        {{ $referal->code ?? '' }} - {{ $referal->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.name') }}
                        </th>
                        <td>
                            {{ $martregister->name  }} ({{ $martregister->customers->code ?? '' }} - {{ $martregister->customers->name ?? '' }})
                        </td>
                    </tr>                    
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.email') }}
                        </th>
                        <td>
                            {{ $martregister->email  }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.phone') }}
                        </th>
                        <td>
                            {{ $martregister->phone  }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.address') }}
                        </th>
                        <td>
                            {{ $martregister->address  }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.type') }}
                        </th>
                        <td>
                            {{ $martregister->type }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                        {{ trans('global.martregister.fields.status') }}
                        </th>
                        <td>
                            {{ $martregister->status }}
                        </td>
                    </tr>
                    
                </tbody>
            </table>
            <a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>


    </div>
</div>
@endsection