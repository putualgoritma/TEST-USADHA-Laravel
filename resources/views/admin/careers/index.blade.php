@extends('layouts.admin')
@section('content')
@can('career_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.careers.create") }}">
                Inisiasi {{ trans('global.career.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.career.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
    <div class="form-group">
    <div class="col-md-6">
    <form action="" id="filtersForm">
    <div class="form-group">
                    <div class="input-group">
                    <select name="status" id="status" class="form-control">
                        <option value="">== Semua Status ==</option>
                        <option value="pending">Pending</option>
                        <option value="active">Aktif</option>
                    </select>
                </div>
               </div>
               <span class="input-group-btn">
                    <input type="submit" class="btn btn-primary" value="Filter">
                </span>
            </form>
</form>
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-careers">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            No.
                        </th>
                        <th>
                            {{ trans('global.member.fields.code') }}
                        </th> 
                        <th>
                            {{ trans('global.member.fields.register') }}
                        </th>                        
                        <th>
                            {{ trans('global.member.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.career.fields.name') }}
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
@can('career_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.careers.massDestroy') }}",
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
    career: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-careers:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.careers.index') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        'status':  $("#status").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'DT_RowIndex', name: 'no' },
        { data: 'code', name: 'code' },
        { data: 'register', name: 'register' },
        { data: 'name', name: 'name' },
        { data: 'careertype_name', name: 'careertype_name', searchable: false },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
  };

  $('.datatable-careers').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });

})

</script>
@endsection
@endsection
