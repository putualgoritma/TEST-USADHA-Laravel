@extends('layouts.admin')
@section('content')
@can('martregister_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.martregisters.create") }}">
                {{ trans('global.add') }} {{ trans('global.martregister.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.martregister.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bmartregistered table-striped table-hover datatable datatable-Order">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.martregister.fields.id') }}
                        </th>
                        <th>
                            {{ trans('global.martregister.fields.register') }}
                        </th>
                        <th>
                            {{ trans('global.martregister.fields.customer_id') }}
                        </th>
                        <th>
                            {{ trans('global.martregister.fields.type') }}
                        </th>
                        <th>
                            {{ trans('global.martregister.fields.status') }}
                        </th>                        
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($martregisters as $key => $martregister)
                        <tr data-entry-id="{{ $martregister->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $martregister->id ?? '' }}
                            </td>
                            <td>
                                {{ $martregister->created_at ?? '' }}
                            </td>
                            <td>
                            {{ $martregister->customers->code ?? '' }} - {{ $martregister->customers->name ?? '' }}
                            </td>
                            <td>
                                {{ $martregister->type ?? '' }}
                            </td>
                            <td>
                                {{ $martregister->status ?? '' }}
                            </td>
                            <td>
                                @can('martregister_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.martregisters.show', $martregister->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('martregister_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.martregisters.edit', $martregister->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan
                                    @if($martregister->status =='pending')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.martregisters.approved', $martregister->id) }}">
                                        Setujui
                                    </a>
                                    @endif
                                    @if($martregister->status =='pending')
                                    <a class="btn btn-xs btn-warning" href="{{ route('admin.martregisters.cancelled', $martregister->id) }}">
                                        Batalkan
                                    </a>
                                    @endif
                                @can('martregister_delete')
                                    <form action="{{ route('admin.martregisters.destroy', $martregister->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('martregister_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.martregisters.massDestroy') }}",
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
    martregister: [[ 1, 'desc' ]],
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