@can($viewGate)
    <a class="btn btn-xs btn-success" href="{{ route('admin.agents.stock', ['product'=>$row->id,'customer'=>$row->owner]) }}">
        Mutasi Stok
    </a>
@endcan
