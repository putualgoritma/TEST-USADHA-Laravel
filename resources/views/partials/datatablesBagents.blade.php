@can($viewGate)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.agents.buyDetails', ['customer'=>$row->id]) }}">
        Rincian
    </a>
@endcan
