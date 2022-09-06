@extends('layouts.admin')
@section('content')
@can('topup_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.topups.create") }}">
                {{ trans('global.add') }} {{ trans('global.topup.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.topup.title_singular') }} {{ trans('global.list') }}
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
                            Tipe Pembayaran
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
                    @foreach($topups as $key => $topup)
                        <tr data-entry-id="{{ $topup->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $topup->id ?? '' }}
                            </td>
                            <td>
                                {{ $topup->customers->name ?? '' }}
                            </td>
                            <td>
                                {{ $topup->code ?? '' }}
                            </td>
                            <td>
                                {{ $topup->register ?? '' }}
                            </td>
                            <td>
                                <ul>
                                @foreach($topup->points as $key => $item)
                                    <li>{{ $item->name }} (Rp. {{ $item->pivot->amount }})</li>
                                @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ $account->name ?? '' }}
                            </td>
                            <td>
                                {{ $topup->status ?? '' }}
                            </td>
                            <td>
                                @can('topup_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.topups.show', $topup->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('topup_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.topups.edit', $topup->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                    @if($topup->status =='pending')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.topups.approved', $topup->id) }}">
                                        Setujui
                                    </a>
                                    @endif
                                    @if($topup->status =='pending')
                                    <a class="btn btn-xs btn-warning" href="{{ route('admin.topups.cancelled', $topup->id) }}">
                                        Batalkan
                                    </a>
                                    @endif
                                @can('topup_delete')
                                    <form action="{{ route('admin.topups.destroy', $topup->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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