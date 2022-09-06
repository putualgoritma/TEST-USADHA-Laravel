@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.orderpoint.title') }}
    </div>

    <div class="card-body">
    <div class="form-group">
        <form action="" id="filtersForm"> 
            <div class="col-md-6">
                {{-- <div class="row"> --}}
                    {{-- <div class="col-md-6"> --}}
                        <div class="form-group">
                            <div class="input-group">
                                <select id="customer" name="customer" class="form-control">
                                <option value="">== Semua User ==</option>
                                @foreach($customers as $customer)
                                <option value="{{$customer->id}}">{{ $customer->code}} - {{ $customer->name}}</option>
                                @endforeach
                                </select>
                            </div>               
                        </div>
                    {{-- </div>
                        {{-- <div class="col-md-6"> --}}
                        <div class="form-group">
                            <div class="input-group">
                                <select id="point" name="point" class="form-control">
                                <option value="">== Semua Jenis Poin ==</option>
                                @foreach($points as $point)
                                <option value="{{$point->id}}">{{ $point->code}} - {{ $point->name}}</option>
                                @endforeach
                                </select>
                            </div>               
                        </div>
                    {{-- </div>
                    <div class="col-md-6"> --}}
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
                    {{-- </div> --}}
                 {{-- </div> --}}
                 <span class="input-group-btn">
                    <input type="submit" class="btn btn-primary" value="Filter">
                </span>
            </div>
        </form>
        <div class="row">
            </div>
        <div class="table-responsive">
        <table class="table table-bordered table-striped ajaxTable datatable-points">
                <thead>
                    <tr>
                        <th width="10">
                            
                        </th>
                        <th>
                            No.
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
                        <th>
                            Saldo
                        </th>
                    </tr>                    
                </thead>  
                <tfoot align="left">
		            <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
	            </tfoot>              
            </table>
        </div>
    </div>
</div>

@endsection
@section('scripts')
@parent
<script>
    $(function () {
    let searchParams = new URLSearchParams(window.location.search)
    let customer = searchParams.get('customer')
    if (customer) {
        $("#customer").val(customer);
    }
    let point = searchParams.get('point')
    if (point) {
        $("#point").val(point);
    }
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

    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    paging: true,
    ajax: {
      url: "{{ route('admin.history-points') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        'customer':  $("#customer").val(),
        'point':  $("#point").val(),
        'from' :   $("#from").val(),
        'to' :  $("#to").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no', searchable: false },
        { data: 'register', name: 'register' ,  searchable: false },
        { data: 'memo', name: 'memo' },
        { data: 'name', name: 'name', searchable: false },                
        { data: 'debit', name: 'debit',searchable: false },
        { data: 'credit', name: 'credit', searchable: false },
        { data: 'balance', name: 'balance', searchable: false },
    ],
    pageLength: 100,
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // converting to interger to find total
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // computing column Total of the complete result 
            var debitTotal = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            var creditTotal = api
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            var balanceTotal = debitTotal - creditTotal;
				
	    // Update footer by showing the total with the reference of the column index 
	    $( api.column( 4 ).footer() ).html('Total');
        $( api.column( 5 ).footer() ).html(debitTotal.toLocaleString("en-GB"));
        $( api.column( 6 ).footer() ).html(creditTotal.toLocaleString("en-GB"));
        $( api.column( 7 ).footer() ).html(balanceTotal.toLocaleString("en-GB"));
        },
  };

  $('.datatable-points').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
    
})

</script>
@endsection
<script type="text/javascript">
    $(function(){
     $(".datepicker").datepicker({
         format: 'yyyy-mm-dd',
         autoclose: true,
         todayHighlight: true,
     });
    });
</script>