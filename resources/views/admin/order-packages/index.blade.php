@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.product.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <form action="" id="filtersForm"> 
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                            <div class="input-group">
                                <select id="product" name="product" class="form-control">
                                <option value="">== Semua User ==</option>
                                @foreach($products as $product)
                                <option value="{{$product->id}}">{{ $product->code}} - {{ $product->name}}</option>
                                @endforeach
                                </select>
                            </div>  
                            </div>
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
                </div>
            </form>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover  datatable ajaxTable datatable-orderproduct">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.order.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.product.fields.name') }}
                        </th>
                        <th>
                            Debit
                        </th>
                        <th>
                            Credit
                        </th>
                    </tr>
                </thead>     
                <tfoot align="left">
                        <tr><th></th><th></th><th></th><th></th><th></th><th></th></tr>
                    </tfoot>           
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {
    let searchParams = new URLSearchParams(window.location.search)
    let product = searchParams.get('product')
    if (product) {
        $("#product").val(product);
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
@can('order_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.orders.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-orderproduct:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.order-package.index') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        'product':  $("#product").val(),
        'from' :   $("#from").val(),
        'to' :  $("#to").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no', searchable: false },
        { data: 'register', name: 'register' },
        { data: 'name', name: 'name', searchable: false  },
        { data: 'debit', name: 'debit', searchable: false },
        { data: 'credit', name: 'credit', searchable: false }
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
 
            // computing column Debit of the complete result 
            var Debit = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            // computing column Credit of the complete result 
            var Credit = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
	    // Update footer by showing the total with the reference of the column index 
	    $( api.column( 3 ).footer() ).html('Total');
        $( api.column( 4 ).footer() ).html(Debit.toLocaleString("en-GB"));
        $( api.column( 5 ).footer() ).html(Credit.toLocaleString("en-GB"));
        },
  };

  $('.datatable-orderproduct').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
    
})

</script>
@endsection
@endsection