@extends('layouts.admin')
@section('content')
@can('capitalist_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.capitalists.create") }}">
                {{ trans('global.add') }} {{ trans('global.capitalist.title_singular') }}
            </a>
        </div>
    </div>
@endcan
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="card">
    <div class="card-header">
        {{ trans('global.capitalist.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">
                <form action="" id="filtersForm">
                    <div class="input-group">
                    
                    <select name="status-filter" id="status-filter" class="form-control">
                    <option value="">== Semua Status ==</option>
                    <option value="pending">Pending</option>
                    <option value="active">Aktif</option>
                    </select>
                    <span class="input-group-btn">
                        <input type="submit" class="btn btn-primary" value="Filter">
                    </span> 
                    </div>
                </form>
                </div>
            </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-capitalists">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.capitalist.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.capitalist.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.capitalist.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.capitalist.fields.address') }}
                        </th>
                        <th>
                            {{ trans('global.capitalist.fields.phone') }}
                        </th>
                        <th>
                            {{ trans('global.member.fields.status') }}
                        </th>
                        <th>
                            Saldo Poin
                        </th>
                        <th>
                            &nbsp;
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
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let statusFilter = searchParams.get('status-filter')
  if (statusFilter) {
    $("#status-filter").val(statusFilter);
  }else{
    $("#status-filter").val('');
  }  
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.capitalists.massDestroy') }}",
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
@can('capitalist_delete')
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
      url: "{{ route('admin.capitalists.index') }}",
      data: {
        'status': searchParams.get('status-filter'),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'code', name: 'code' },
        { data: 'register', name: 'register' },
        { data: 'name', name: 'name' },
        { data: 'address', name: 'address' },
        { data: 'phone', name: 'phone' },
        { data: 'status', name: 'status' },
        { data: 'saldo', name: 'saldo' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 1, 'asc' ]],
    pageLength: 100,
  };

  $('.datatable-capitalists').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection