@extends('layouts.admin')
@section('content')
@can('payable_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.payables.create") }}">
                {{ trans('global.add') }} {{ trans('global.payable.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.payable.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
                <form action="" id="filtersForm"> 
                <div class="input-group">
                    <select id="customer" name="customer" class="form-control">
                    <option value="">== Semua Debitur ==</option>
                    @foreach($customers as $customer)
                    <option value="{{$customer->id}}">{{ $customer->name}}</option>
                    @endforeach
                    </select>
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                </div>                
                </form>
                </div>
            </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-payables">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.payable.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.payable.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.payable.fields.customer_id') }}
                        </th>
                        <th>
                            {{ trans('global.payable.fields.memo') }}
                        </th>
                        <th>
                            {{ trans('global.payable.fields.amount') }}
                        </th>
                        <th>
                            {{ trans('global.payable.fields.amount_balance') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tfoot align="left">
		            <tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>
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
  let customer = searchParams.get('customer')
  if (customer) {
    $("#customer").val(customer);
  }else{
    $("#customer").val('');
  }

  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.payables.massDestroy') }}",
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
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('payable_delete')
  dtButtons.push(deleteButton)
@endcan

  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.payables.index') }}",
      data: {
        'customer': searchParams.get('customer'),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'register', name: 'register' },
        { data: 'code', name: 'code' },
        { data: 'name', name: 'name' },
        { data: 'memo', name: 'memo' },
        { data: 'amount', name: 'amount' },
        { data: 'amount_balance', name: 'amount_balance' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 1, 'asc' ]],
    pageLength: 100,
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
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            var resTotal = api
                .column( 7 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
				
	    // Update footer by showing the total with the reference of the column index 
	    $( api.column( 5 ).footer() ).html('Total');
        $( api.column( 6 ).footer() ).html(Total.toLocaleString("en-GB"));
        $( api.column( 7 ).footer() ).html(resTotal.toLocaleString("en-GB"));
        },
  };

  $('.datatable-payables').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });

})

</script>
@endsection
@endsection