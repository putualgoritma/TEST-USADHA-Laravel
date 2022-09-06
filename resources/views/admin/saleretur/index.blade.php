@extends('layouts.admin')
@section('content')
@can('saleretur_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.salereturs.create") }}">
                {{ trans('global.add') }} {{ trans('global.saleretur.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.saleretur.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Order">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.saleretur.fields.id') }}
                        </th>
                        <th>
                            {{ trans('global.saleretur.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.saleretur.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.saleretur.fields.customers_id') }}
                        </th>
                        <th>
                                Status Order
                            </th>
                            <th>
                                Status Delivery
                            </th>
                        <th>
                            {{ trans('global.saleretur.fields.memo') }}
                        </th>
                        <th>
                            Rekening
                        </th>
                        <th>
                            Total
                        </th>
                        <th>
                            {{ trans('global.saleretur.fields.products') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salereturs as $key => $saleretur)
                        <tr data-entry-id="{{ $saleretur->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $saleretur->id ?? '' }}
                            </td>
                            <td>
                                {{ $saleretur->code ?? '' }}
                            </td>
                            <td>
                                {{ $saleretur->register ?? '' }}
                            </td>
                            <td>
                                {{ $saleretur->customers->code ?? '' }} - {{ $saleretur->customers->name ?? '' }}
                            </td>
                            <td>
                            {{ $saleretur->status ?? '' }}
                            </td>
                            <td>
                            {{ $saleretur->status_delivery ?? '' }}
                            </td>
                            <td>
                            {{ $saleretur->memo ?? '' }}
                            </td>
                            <td>
                            {{ $saleretur->accounts->name ?? '' }}
                            </td>
                            <td>
                            {{ $saleretur->total ?? '' }}
                            </td>
                            <td>
                                <ul>
                                @foreach($saleretur->products as $key => $item)
                                    <li>{{ $item->name }} ({{ $item->pivot->quantity }} x ${{ $item->price }})</li>
                                @endforeach
                                </ul>
                            </td>
                            <td>
                                @can('saleretur_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.salereturs.show', $saleretur->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('saleretur_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.salereturs.edit', $saleretur->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('saleretur_delete')
                                    <form action="{{ route('admin.salereturs.destroy', $saleretur->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('saleretur_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.salereturs.massDestroy') }}",
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
    saleretur: [[ 1, 'desc' ]],
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