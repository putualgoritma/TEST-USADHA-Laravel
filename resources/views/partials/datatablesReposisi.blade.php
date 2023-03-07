
    @if($row['slot_x'] >0)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.trees.view', ['id'=>$row['id']]) }}">
        Pohon Jaringan
    </a>
    @endif
