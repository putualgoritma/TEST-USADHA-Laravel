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
@if($row->type !='activation_member' && $row->status =='approved' && $crudRoutePart=='orders')
@can($viewGate)
    <form action="{{ route('admin.' . $crudRoutePart . '.cancell') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_id" value="{{ $row->id }}">
        <input type="submit" class="btn btn-xs btn-warning" value="Batalkan">
    </form>
@endcan
@endif
@if($row->status =='approved' && $crudRoutePart=='orders')
@can($viewGate)
    <form action="{{ route('admin.' . $crudRoutePart . '.unblock') }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_id" value="{{ $row->id }}">
        <input type="submit" class="btn btn-xs btn-success" value="Unblock Poin">
    </form>
@endcan
@endif

@if(($row->type =='sale' || $row->type =='stock_trsf') && $row->status =='pending' && $crudRoutePart=='orders')
@can($viewGate)
<a class="btn btn-xs btn-success" href="{{ route('admin.' . $crudRoutePart . '.approved', $row->id) }}">
        Setujui
    </a>
@endcan
@endif

@can($deleteGate)
    <form action="{{ route('admin.' . $crudRoutePart . '.destroy', $row->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
    </form>
@endcan
