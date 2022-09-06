@extends('layouts.admin')
@section('content')
@can('receivable_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.receivables.create") }}">
                {{ trans('global.add') }} {{ trans('global.receivable.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.receivable.title_singular') }} {{ trans('global.list') }}
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
                </div>
                <div class="input-group">
                &nbsp;
                </div>
                    <div class="input-group">
                    
                    <select name="status" id="status" class="form-control">
                    <option value="">== Semua Status ==</option>
                    <option value="pending">Pending</option>
                    <option value="active">Aktif</option>
                    </select>
                    <span class="input-group-btn">
                    &nbsp;&nbsp;<input type="submit" class="btn btn-primary" value="Filter">
                    </span> 
                    </div>
                </form>
                </div>
            </div>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.receivable.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.receivable.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.receivable.fields.customer_id') }}
                        </th>
                        <th>
                            {{ trans('global.receivable.fields.amount') }}
                        </th>
                        <th>
                            {{ trans('global.receivable.fields.amount_balance') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receivables as $key => $receivable)
                        <tr data-entry-id="{{ $receivable->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $receivable->register ?? '' }}
                            </td>
                            <td>
                                {{ $receivable->code ?? '' }}
                            </td>
                            <td>
                                {{ $receivable->customers->name ?? '' }}
                            </td>
                            <td>
                                {{ $receivable->amount ?? '' }}
                            </td>
                            <td>
                                {{ $receivable->amount_balance ?? '' }}
                            </td>
                            <td>
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.receivables.indexTrs', $receivable->id) }}">
                                        Pembayaran
                                    </a>
                                @can('receivable_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.receivables.show', $receivable->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan
                                @can('receivable_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.receivables.edit', $receivable->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('receivable_delete')
                                    <form action="{{ route('admin.receivables.destroy', $receivable->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </tbody>
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
  
  let customer = searchParams.get('customer')
  if (customer) {
    $("#customer").val(customer);
  }else{
    $("#customer").val('');
  }
  let status = searchParams.get('status')
  if (status) {
    $("#status").val(status);
  }else{
    $("#status").val('');
  }

  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.receivables.massDestroy') }}",
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
@can('receivable_delete')
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
      url: "{{ route('admin.members.index') }}",
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
        { data: 'email', name: 'email' },
        { data: 'phone', name: 'phone' },
        { data: 'status', name: 'status' },
        { data: 'saldo', name: 'saldo' },
        { data: 'actions', name: '{{ trans('global.actions') }}' }
    ],
    order: [[ 1, 'asc' ]],
    pageLength: 100,
  };

  $('.datatable-members').DataTable(dtOverrideGlobals);
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });

});

</script>
@endsection
@endsection