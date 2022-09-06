@extends('layouts.admin')
@section('content')
@can('receivable_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.receivables.createTrs', $id) }}">
                {{ trans('global.add') }} {{ trans('global.receivable.fields.trs') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.receivable.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
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
                            {{ trans('global.receivable.fields.type') }}
                        </th>
                        <th>
                            {{ trans('global.receivable.fields.amount') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receivabletrs as $key => $receivable)
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
                                {{ $customer->name ?? '' }}
                            </td>
                            <td>
                                {{ $receivable->type ?? '' }}
                            </td>
                            <td>
                                {{ $receivable->amount ?? '' }}
                            </td>
                            <td>
                                @can('receivable_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.receivables.showTrs', $receivable->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan
                                @can('receivable_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.receivables.editTrs', $receivable->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                @can('receivable_delete')
                                    <form action="{{ route('admin.receivables.destroyTrs', $receivable->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
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
})

</script>
@endsection
@endsection