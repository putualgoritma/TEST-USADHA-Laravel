@extends('layouts.admin')
@section('content')
@can('topup_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.transfer.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-btopuped table-striped table-hover datatable datatable-Order">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.topup.fields.id') }}
                        </th>
                        <th>
                            Nama
                        </th>
                        <th>
                            {{ trans('global.topup.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.topup.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.topup.fields.points') }}
                        </th>
                        <th>
                            Memo
                        </th>
                        <th>
                            Status
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfers as $key => $transfer)
                        <tr data-entry-id="{{ $transfer->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $transfer->id ?? '' }}
                            </td>
                            <td>
                                {{ $transfer->customers->name ?? '' }}
                            </td>
                            <td>
                                {{ $transfer->code ?? '' }}
                            </td>
                            <td>
                                {{ $transfer->register ?? '' }}
                            </td>
                            <td>
                                <ul>
                                @foreach($transfer->points as $key => $item)
                                    <li>{{ $item->name }} (Rp. {{ $item->pivot->amount }})</li>
                                @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ $transfer->memo ?? '' }}
                            </td>
                            <td>
                                {{ $transfer->status ?? '' }}
                            </td>
                            <td>
                                @if($transfer->status =='pending')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.transfers.approved', $transfer->id) }}">
                                        Setujui
                                    </a>
                                    @endif
                                    @if($transfer->status =='pending')
                                    <a class="btn btn-xs btn-warning" href="{{ route('admin.transfers.cancelled', $transfer->id) }}">
                                        Batalkan
                                    </a>
                                    @endif                                

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('topup_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.topups.massDestroy') }}",
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
    topup: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-Order:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection