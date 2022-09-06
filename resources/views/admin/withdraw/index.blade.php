@extends('layouts.admin')
@section('content')
@can('withdraw_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.withdraw.create") }}">
                {{ trans('global.add') }} {{ trans('global.withdraw.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.withdraw.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-btopuped table-striped table-hover datatable datatable-Order">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.withdraw.fields.id') }}
                        </th>
                        <th>
                            Nama
                        </th>
                        <th>
                            {{ trans('global.withdraw.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.withdraw.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.withdraw.fields.points') }}
                        </th>
                        <th>
                            {{ trans('global.withdraw.fields.bank_name') }}
                        </th>
                        <th>
                            {{ trans('global.withdraw.fields.bank_acc_no') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withdraw as $key => $withdraw)
                        <tr data-entry-id="{{ $withdraw->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $withdraw->id ?? '' }}
                            </td>
                            <td>
                                {{ $withdraw->customers->name ?? '' }}
                            </td>
                            <td>
                                {{ $withdraw->code ?? '' }}
                            </td>
                            <td>
                                {{ $withdraw->register ?? '' }}
                            </td>
                            <td>
                                <ul>
                                @foreach($withdraw->points as $key => $item)
                                    <li>{{ $item->name }} (Rp. {{ $item->pivot->amount }})</li>
                                @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ $withdraw->accounts->name ?? '' }}
                            </td>
                            <td>
                                {{ $withdraw->bank_acc_no ?? '' }}
                            </td>
                            <td>
                                @can('withdraw_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.withdraw.show', $withdraw->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('withdraw_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.withdraw.edit', $withdraw->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                    @if($withdraw->status =='pending')
                                    <a class="btn btn-xs btn-warning" href="{{ route('admin.withdraw.approved', $withdraw->id) }}">
                                        Approved
                                    </a>
                                    @endif
                                @can('withdraw_delete')
                                    <form action="{{ route('admin.withdraw.destroy', $withdraw->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('withdraw_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.withdraw.massDestroy') }}",
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
    withdraw: [[ 1, 'desc' ]],
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