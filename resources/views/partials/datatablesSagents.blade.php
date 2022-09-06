@can($viewGate)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.agents.saleDetails', ['customer'=>$row->id]) }}">
        Rincian
    </a>
@endcan
