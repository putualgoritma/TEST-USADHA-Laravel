@extends('layouts.admin')
@section('content')
@can('careertype_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.careertypes.create") }}">
                {{ trans('global.add') }} {{ trans('global.careertype.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.careertype.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <div class="form-group">
    <form action="" id="filtersForm">
                <div class="col-md-12">
                    <div class="row">
                        
                    </div>                    
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-careertypes">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.careertype.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.careertype.fields.activation_type_id') }}
                        </th>
                        <th>
                            {{ trans('global.careertype.fields.ro_min_bv') }}
                        </th>
                        <th>
                            {{ trans('global.careertype.fields.fee_min') }}
                        </th>
                        <th>
                            {{ trans('global.careertype.fields.fee_max') }}
                        </th>
                        <th>
                            {{ trans('global.careertype.fields.ref_downline_id') }}
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
    let status = searchParams.get('status')
    if (status) {
        $("#status").val(status);
    }

    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('careertype_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.careertypes.massDestroy') }}",
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
    careertype: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-careertypes:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.careertypes.index') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'name', name: 'name' },
        { data: 'activation_type_id', name: 'activation_type_id' },
        { data: 'ro_min_bv', name: 'ro_min_bv', searchable: false  },
        { data: 'fee_min', name: 'fee_min', searchable: false },
        { data: 'fee_max', name: 'fee_max', searchable: false },
        { data: 'ref_downline_id', name: 'ref_downline_id', searchable: false },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
  };

  $('.datatable-careertypes').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });

})

</script>
@endsection
@endsection
