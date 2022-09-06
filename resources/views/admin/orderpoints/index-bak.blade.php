@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.orderpoint.title') }}
    </div>

    <div class="card-body">
        <div class="row">
            </div>
        <div class="table-responsive">
        <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.orderpoint.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.orderpoint.fields.memo') }}
                        </th>
                        <th>
                            Pengguna
                        </th>
                        <th>
                            Saldo Debit (D)
                        </th>
                        <th>
                            Saldo Credit (C)
                        </th>
                    </tr>
                    @foreach ($orderpoints as $id => $orderpoint)
                    <tr>
                        <td>

                        </td>
                        <td>
                            {{ $orderpoint->orders->register }}
                        </td>
                        <td>
                            {{ $orderpoint->memo }}
                        </td>
                        <td>
                        {{ $orderpoint->customers->code }} - {{ $orderpoint->customers->name }}
                        </td>
                        <td>
                        @if($orderpoint->type === 'D')
                        {{ number_format($orderpoint->amount, 2) }}
                        @endif
                        </td>
                        <td>
                        @if($orderpoint->type === 'C')
                        {{ number_format($orderpoint->amount, 2) }}
                        @endif
                        </td>
                    </tr>
                    @endforeach
                </thead>                
            </table>
        </div>
    </div>
</div>

@endsection