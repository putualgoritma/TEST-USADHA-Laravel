@extends('layouts.admin')
@section('content')
@can('package_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.packages.create") }}">
                {{ trans('global.add') }} {{ trans('global.package.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('global.package.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-package">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.package.fields.name') }}
                        </th>
                        <th>
                            {{ trans('global.package.fields.description') }}
                        </th>
                        <th>
                            {{ trans('global.package.fields.price') }}
                        </th>
                        <th>
                            BV
                        </th>
                        <th>
                            {{ trans('global.package.fields.products') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $key => $package)
                        <tr data-entry-id="{{ $package->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $package->name ?? '' }}
                            </td>
                            <td>
                                {{ $package->description ?? '' }}
                            </td>
                            <td>
                                {{ $package->price ?? '' }}
                            </td>
                            <td>
                                {{ $package->bv ?? '' }}
                            </td>
                            <td>
                                <ul>
                                @foreach($package->products as $key => $item)
                                    <li>{{ $item->name }} ({{ $item->pivot->quantity }} x ${{ $item->price }})</li>
                                @endforeach
                                </ul>
                            </td>
                            <td>
                                @can('package_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.packages.show', $package->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('package_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.packages.edit', $package->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('package_delete')
                                    <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>
                                @endcan
                                @can('package_show')
                                    <a class="btn btn-xs btn-success" href="{{ route('admin.order-package.index', ['product' => $package->id,'from' => '', 'to' => '']) }}">
                                        Mutasi
                                    </a>
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
@can('package_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.packages.massDestroy') }}",
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
    package: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-package:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection