@extends('layouts.admin')
@section('content')
@can('activation_type_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.activation-type.create") }}">
                {{ trans('global.add') }} {{ trans('global.activation_type.title_singular') }}
            </a>
        </div>
    </div>
@endcan
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="card">
    <div class="card-header">
        {{ trans('global.activation_type.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <div class="form-group">
        <div class="col-md-6">                
                </div>
            </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-activation_type">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.activation_type.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.activation_type.fields.type') }}
                        </th>
                        <th>
                            {{ trans('global.activation_type.fields.bv_min') }}
                        </th>
                        <th>
                            {{ trans('global.activation_type.fields.bv_max') }}
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
    url: "{{ route('admin.activationtype.massDestroy') }}",
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
@can('activation_type_delete')
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
      url: "{{ route('admin.activation-type.index') }}",
      data: {
        
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'name', name: 'name' },
        { data: 'type', name: 'type' },
        { data: 'bv_min', name: 'bv_min' },
        { data: 'bv_max', name: 'bv_max' },        
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 1, 'asc' ]],
    pageLength: 100,
  };

  $('.datatable-activation_type').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
@endsection