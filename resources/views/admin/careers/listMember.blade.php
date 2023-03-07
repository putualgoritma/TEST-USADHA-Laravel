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
      url: "{{ route('admin.careers.listMember') }}",
      dataType: "json",
      headers: {'x-csrf-token': _token},
      method: 'GET',
      data: {
        
      }
    },
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'code', name: 'code' },
        { data: 'name', name: 'name' },
        { data: 'address', name: 'address' },  
        { data: 'type', name: 'type' },
        { data: 'status', name: 'status' },      
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
