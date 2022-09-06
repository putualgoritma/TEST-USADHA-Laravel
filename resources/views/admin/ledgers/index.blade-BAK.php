@extends('layouts.admin')
@section('content')
@can('ledger_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.ledgers.create") }}">
                {{ trans('global.add') }} {{ trans('global.ledger.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.ledger.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-ledger">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.ledger.fields.id') }}
                        </th>
                        <th>
                            {{ trans('global.ledger.fields.customers_id') }}
                        </th>
                        <th>
                            {{ trans('global.ledger.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.ledger.fields.memo') }}
                        </th>
                        <th>
                            {{ trans('global.ledger.fields.accounts') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ledgers as $key => $ledger)
                        <tr data-entry-id="{{ $ledger->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $ledger->id ?? '' }}
                            </td>
                            <td>
                                {{ $ledger->customers_id ?? '' }}
                            </td>
                            <td>
                                {{ $ledger->register ?? '' }}
                            </td>
                            <td>
                                {{ $ledger->memo ?? '' }}
                            </td>
                            <td>
                                <ul>
                                @foreach($ledger->accounts as $key => $item)
                                    <li>{{ $item->name }} Rp.{{ number_format($item->pivot->amount,2) }}({{ $item->pivot->entry_type }})</li>
                                @endforeach
                                </ul>
                            </td>
                            <td>
                                @can('ledger_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.ledgers.show', $ledger->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('ledger_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.ledgers.edit', $ledger->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('ledger_delete')
                                    <form action="{{ route('admin.ledgers.destroy', $ledger->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('ledger_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.ledgers.massDestroy') }}",
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
    ledger: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-ledger:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection