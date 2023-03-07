
    @if($row['slot_x'] >0)
    <a class="btn btn-xs btn-primary" href="{{ route('admin.careers.create', ['id'=>$row['id']]) }}">
        Pilih
    </a>
    @endif
