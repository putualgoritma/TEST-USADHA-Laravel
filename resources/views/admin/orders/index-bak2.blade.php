@extends('layouts.admin')
@section('content')
@can('order_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.orders.create") }}">
                {{ trans('global.add') }} {{ trans('global.order.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.order.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
                <form action="" id="filtersForm"> 
                <div class="input-group">
                    <select id="customer" name="customer" class="form-control">
                    <option value="">== Semua User ==</option>
                    @foreach($customers as $customer)
                    <option value="{{$customer->id}}">{{ $customer->code}} - {{ $customer->name}}</option>
                    @endforeach
                    </select>
                </div> 
                <div class="row">
                &nbsp;
                </div> 
                <div class="input-group">
                    <select id="type" name="type" class="form-control">
                    <option value="">== Semua Type ==</option>
                    <option value="sale">Penjualan</option>
                    <option value="sale_retur">Retur Penjualan</option>
                    <option value="topup">Topup</option>
                    <option value="transfer">Transfer</option>
                    <option value="withdraw">Withdraw</option>
                    <option value="agent_sale">Penjualan Agen</option>
                    <option value="production">Produksi</option>                    
                    <option value="buy">Pembelian</option>                    
                    <option value="buy_retur">Retur Pembelian</option>
                    <option value="stock_trsf">Transfer Stok</option>                    
                    </select>
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                </div>                
                </form>
                </div>
            </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-orders"">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.order.fields.id') }}
                        </th>
                        <th>
                            {{ trans('global.order.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.order.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.order.fields.customers_id') }}
                        </th>
                        <th>
                                Status Order
                            </th>
                            <th>
                                Status Delivery
                            </th>
                        <th>
                            {{ trans('global.order.fields.memo') }}
                        </th>
                        <th>
                            Rekening
                        </th>
                        <th>
                            Total
                        </th>
                        <th>
                            {{ trans('global.order.fields.products') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tfoot align="left">
		            <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
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
    let type = searchParams.get('type')
    if (type) {
        $("#type").val(type);
    }else{
        $("#type").val('');
    }
    let customer = searchParams.get('customer')
    if (customer) {
        $("#customer").val(customer);
    }else{
        $("#customer").val('');
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
  $('.datatable-orders:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.orders.index') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        'type': searchParams.get('type'),
        'customer': searchParams.get('customer'),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'code', name: 'code' },
        { data: 'register', name: 'register' },
        { data: 'name', name: 'name' },
        { data: 'status', name: 'status' },
        { data: 'status_delivery', name: 'status_delivery' },
        { data: 'memo', name: 'memo' },        
        { data: 'accpay', name: 'accpay' },
        { data: 'amount', name: 'amount' },
        { data: 'product', name: 'product' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
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
            var Total = api
                .column( 9 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
	    // Update footer by showing the total with the reference of the column index 
	    $( api.column( 8 ).footer() ).html('Total');
        $( api.column( 9 ).footer() ).html(Total.toLocaleString("en-GB"));
        },
  };

  $('.datatable-orders').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
    
})

</script>
@endsection