@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.account.profit_loss') }}
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
                            Saldo
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Pendapatan/Revenue
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @php
                    $total_revenue = 0;
                    @endphp
                    @foreach ($accounts_revenues as $id => $account)
                    @php
                    $amount = $account->amount_credit - $account->amount_debit;
                    $total_revenue = $total_revenue + $amount;
                    @endphp
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
                            {{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Total Pendapatan
                        </th>
                        <th>
                        {{ number_format($total_revenue, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Biaya/Expense
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @php
                    $total_expense = 0;
                    @endphp
                    @foreach ($accounts_expenses as $id => $account)
                    @php
                    $amount = $account->amount_debit - $account->amount_credit;
                    $total_expense = $total_expense + $amount;
                    @endphp
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
                            {{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Total Biaya
                        </th>
                        <th>
                        {{ number_format($total_expense, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                        Laba/Profit
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @php
                    $total = $total_revenue-$total_expense;
                    @endphp
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Total Laba
                        </th>
                        <th>
                        {{ number_format($total, 2) }}
                        </th>
                    </tr>               
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
