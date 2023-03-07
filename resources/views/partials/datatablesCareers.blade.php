@can($viewGate)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.' . $crudRoutePart . '.showMember', ['id'=>$row->id]) }}">
        {{ trans('global.view') }}
    </a>
@endcan