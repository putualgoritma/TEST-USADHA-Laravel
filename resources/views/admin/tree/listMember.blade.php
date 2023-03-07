@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Daftar Member
    </div>

    <div class="card-body">
    <div class="form-group">
    <form action="" id="filtersForm">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <select id="status" name="status" class="form-control">
                                <option value="yes" selected>Sudah Reposisi</option>
                                <option value="no">Belum Reposisi</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="top_id" id="top_id" value="{{$top_id}}">
                        <input type="hidden" name="slot_x" id="slot_x" value="{{$slot_x}}">
                        <input type="hidden" name="slot_y" id="slot_y" value="{{$slot_y}}">
                        <input type="hidden" name="selected_ref_id" id="selected_ref_id" value="{{$selected_ref_id}}">
                        <span class="input-group-btn">
                        <input type="submit" class="btn btn-primary" value="Filter">
                    </span>
                    </div>                    
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable ajaxTable datatable-products">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                           Kode
                        </th>
                        <th>
                            Nama
                        </th>
                        <th>
                            Alamat
                        </th> 
                        <th>
                            Tipe
                        </th>
                        <th>
                            Status
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
    let top_id = searchParams.get('top_id')
    if (top_id) {
        $("#top_id").val(top_id);
    }
    let slot_x = searchParams.get('slot_x')
    if (slot_x) {
        $("#slot_x").val(slot_x);
    }
    let slot_y = searchParams.get('slot_y')
    if (slot_y) {
        $("#slot_y").val(slot_y);
    }
    let selected_ref_id = searchParams.get('selected_ref_id')
    if (selected_ref_id) {
        $("#selected_ref_id").val(selected_ref_id);
    }

    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)

  $.extend(true, $.fn.dataTable.defaults, {
    product: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-products:not(.ajaxTable)').DataTable({ buttons: dtButtons })

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: {
      url: "{{ route('admin.trees.index') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        'status':  $("#status").val(),
        'top_id':  $("#top_id").val(),
        'slot_x':  $("#slot_x").val(),
        'slot_y':  $("#slot_y").val(),
        'selected_ref_id':  $("#selected_ref_id").val(),
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'code', name: 'code' },
        { data: 'name', name: 'name' },
        { data: 'address', name: 'address' },  
        { data: 'type', name: 'type' },
        { data: 'status', render: function (dataField) { return dataField === 0 ?'<button type="button" class="btn btn-warning btn-xs" disabled>Belum</button>':'<button type="button" class="btn btn-primary btn-xs" disabled>Sudah</button>'; } },      
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    pageLength: 100,
    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
  };

  $('.datatable-products').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });

})

</script>
@endsection
@endsection
