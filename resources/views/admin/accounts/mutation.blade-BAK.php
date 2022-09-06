@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.account.mutation') }}
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
                            {{ trans('global.account.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.account.fields.accounts_id') }}
                        </th>
                        <th>
                            {{ trans('global.account.fields.memo') }}
                        </th>
                        <th>
                            Debit (D)
                        </th>
                        <th>
                            Credit (C)
                        </th>
                        <th>
                            Saldo
                        </th>
                    </tr>
                    @php
                    $saldo = 0;
                    @endphp
                    @foreach ($ledger_entries as $id => $entry)
                    <tr>
                        <td>

                        </td>
                        <td>
                            {{ $entry->register }}
                        </td>
                        <td>
                            {{ $entry->name }}
                        </td>
                        <td>
                            {{ $entry->memo }}
                        </td>
                        <td>
                        @if($entry->entry_type=='D')
                            {{ number_format($entry->amount, 2) }}
                            @php
                            $saldo = $saldo+$entry->amount;
                            @endphp
                        @endif
                        </td>
                        <td>
                        @if($entry->entry_type=='C')
                            {{ number_format($entry->amount, 2) }}
                            @php
                            $saldo = $saldo-$entry->amount;
                            @endphp
                        @endif
                        </td>
                        <td>
                            {{ number_format($saldo, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </thead>                
            </table>
        </div>
    </div>
</div>

@endsection