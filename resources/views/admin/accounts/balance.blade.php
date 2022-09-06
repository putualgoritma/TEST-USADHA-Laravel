@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.account.balance') }}
    </div>

    <div class="card-body">
    <form action="" id="filtersForm"> 
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{-- <label>Dari Tanggal</label> --}}
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                    <input id="from" placeholder="masukkan tanggal Awal" type="date" class="form-control datepicker" name="from" value = "">
                                </div>
                            </div>
                            <div class="form-group">
                                {{-- <label>Sampai Tanggal</label> --}}
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                    <input id="to" placeholder="masukkan tanggal Akhir" type="date" class="form-control datepicker" name="to" value = "{{date('Y-m-d')}}">
                                </div>
                            </div>                                         
                        </div>                        
                    </div>
                    <span class="input-group-btn">
                        <input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                    <div class="row">&nbsp;</div>
                </div>
            </form>
        <div class="row">
            </div>
        <div class="table-responsive">
        <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.account.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.account.fields.name') }}
                        </th>
                        <th>
                            Saldo Debit (D)
                        </th>
                        <th>
                            Saldo Credit (C)
                        </th>
                        <th>
                            Saldo
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @foreach ($accounts as $id => $account)
                    <tr>
                        <td>

                        </td>
                        <td>
                            {{ $account->code }}
                        </td>
                        <td>
                            {{ $account->name }}
                        </td>
                        <td>
                            {{ number_format($account->amount_debit, 2) }}
                        </td>
                        <td>
                            {{ number_format($account->amount_credit, 2) }}
                        </td>
                        <td>
                        @php
                            $saldo = $account->amount_debit-$account->amount_credit;
                        @endphp
                            {{ number_format($saldo, 2) }}
                        </td>
                        <td>
                        @can('account_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.acc-mutation', ['account' => $account->id,'from' => '', 'to' => '']) }}">
                                    {{ trans('global.account.mutation') }}
                                    </a>
                                @endcan
                        </td>
                    </tr>
                    @endforeach
                </thead>                
            </table>
        </div>
    </div>
</div>

@section('scripts')
@parent
<script>
    $(function () {
    let searchParams = new URLSearchParams(window.location.search)
    // date from unutk start tanggal 
    let from = searchParams.get('from')
    if (from) {
        $("#from").val(from);
    }

    // date to untuk batas tanggal 
    let to = searchParams.get('to')
    if (to) {
        $("#to").val(to);
    }   
})
</script>
@endsection
@endsection
