@can($viewGate)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.show', $row->id) }}">
        {{ trans('global.view') }}
    </a>
@endcan
@can($editGate)
    <a class="btn btn-xs btn-info" href="{{ route('admin.' . $crudRoutePart . '.edit', $row->id) }}">
        {{ trans('global.edit') }}
    </a>
@endcan
@if($row->status =='active')
@can($editGate)
    <a class="btn btn-xs btn-warning" href="{{ route('admin.members.cancell', $row->id) }}">
        Batalkan Aktivasi
    </a>
    <a class="btn btn-xs btn-info" href="{{ route('admin.' . $crudRoutePart . '.upgrade', $row->id) }}">
        Upgrade Manual
    </a>
@endcan
@endif
@if($row->status_block =='1')
    <a class="btn btn-xs btn-success" href="{{ route('admin.members.unblock', $row->id) }}">
    Unblock
    </a>
@endif
@can($deleteGate)
    <form action="{{ route('admin.' . $crudRoutePart . '.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
    </form>
@endcan
