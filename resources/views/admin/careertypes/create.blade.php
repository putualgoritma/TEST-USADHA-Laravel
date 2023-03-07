@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.careertype.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.careertypes.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.careertype.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($careertype) ? $careertype->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.name_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('activation_type_id') ? 'has-error' : '' }}">
                <label for="activation_type_id">{{ trans('global.careertype.fields.activation_type_id') }}</label>
                <select name="activation_type_id" class="form-control">
                    <option value="">-- choose activation_type --</option>
                    @foreach ($activationtypes as $activation_type)
                        <option value="{{ $activation_type->id }}"{{ old('activation_type_id') == $activation_type->id ? ' selected' : '' }}>
                        {{ $activation_type->name }} {{ $activation_type->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('activation_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('activation_type_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.activation_type_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('ro_min_bv') ? 'has-error' : '' }}">
                <label for="ro_min_bv">{{ trans('global.careertype.fields.ro_min_bv') }}</label>
                <input type="number" id="ro_min_bv" name="ro_min_bv" class="form-control" value="{{ old('ro_min_bv', isset($careertype) ? $careertype->ro_min_bv : '') }}">
                @if($errors->has('ro_min_bv'))
                    <em class="invalid-feedback">
                        {{ $errors->first('ro_min_bv') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.ro_min_bv_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('auto_maintain_bv') ? 'has-error' : '' }}">
                <label for="auto_maintain_bv">{{ trans('global.careertype.fields.auto_maintain_bv') }}</label>
                <input type="number" id="auto_maintain_bv" name="auto_maintain_bv" class="form-control" value="{{ old('auto_maintain_bv', isset($careertype) ? $careertype->auto_maintain_bv : '') }}">
                @if($errors->has('auto_maintain_bv'))
                    <em class="invalid-feedback">
                        {{ $errors->first('auto_maintain_bv') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.auto_maintain_bv_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('fee_am') ? 'has-error' : '' }}">
                <label for="fee_am">{{ trans('global.careertype.fields.fee_am') }}</label>
                <input type="number" id="fee_am" name="fee_am" class="form-control" value="{{ old('fee_am', isset($careertype) ? $careertype->fee_am : '') }}">
                @if($errors->has('fee_am'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fee_am') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.fee_am_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('fee_min') ? 'has-error' : '' }}">
                <label for="fee_min">{{ trans('global.careertype.fields.fee_min') }}</label>
                <input type="number" id="fee_min" name="fee_min" class="form-control" value="{{ old('fee_min', isset($careertype) ? $careertype->fee_min : '') }}">
                @if($errors->has('fee_min'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fee_min') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.fee_min_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('fee_max') ? 'has-error' : '' }}">
                <label for="fee_max">{{ trans('global.careertype.fields.fee_max') }}</label>
                <input type="number" id="fee_max" name="fee_max" class="form-control" value="{{ old('fee_max', isset($careertype) ? $careertype->fee_max : '') }}">
                @if($errors->has('fee_max'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fee_max') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.fee_max_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('ref_downline_num') ? 'has-error' : '' }}">
                <label for="ref_downline_num">{{ trans('global.careertype.fields.ref_downline_num') }}</label>
                <input type="number" id="ref_downline_num" name="ref_downline_num" class="form-control" value="{{ old('ref_downline_num', isset($careertype) ? $careertype->ref_downline_num : '') }}">
                @if($errors->has('ref_downline_num'))
                    <em class="invalid-feedback">
                        {{ $errors->first('ref_downline_num') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.ref_downline_num_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('ref_downline_id') ? 'has-error' : '' }}">
                <label for="ref_downline_id">{{ trans('global.careertype.fields.ref_downline_id') }}</label>
                <select name="ref_downline_id" class="form-control">
                    <option value="">-- choose activation_type --</option>
                    @foreach ($activationtypes as $activation_type)
                        <option value="{{ $activation_type->id }}"{{ old('ref_downline_id') == $activation_type->id ? ' selected' : '' }}>
                        {{ $activation_type->name }} {{ $activation_type->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('ref_downline_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('ref_downline_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.careertype.fields.ref_downline_id_helper') }}
                </p>
            </div>
            <div class="form-group">
                <label for="team_level">{{ trans('global.careertype.fields.team_level') }}:</label>
                <div><label><input type="radio" name="team_level" value="career" checked="checked">Jenjang Karir</label></div>
                <div><label><input type="radio" name="team_level" value="activation">Tipe Aktivasi</label></div>                
            </div>
            <div class="card team_level career">
                <div class="card-header">
                Jenjang Karir Team
                </div>

                <div class="card-body">
                    <table class="table" id="careertypes_table">
                        <thead>
                            <tr>
                                <th>Items</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (old('careertypes', ['']) as $index => $oldcareertype)
                                <tr id="careertype{{ $index }}">
                                    <td>
                                        <select name="careertypes[]" class="form-control">
                                            <option value="">-- choose careertype --</option>
                                            @foreach ($careertypes as $careertypeloop)
                                                <option value="{{ $careertypeloop->id }}"{{ $oldcareertype == $careertypeloop->id ? ' selected' : '' }}>
                                                    {{ $careertypeloop->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="amounts[]" class="form-control" value="{{ old('amounts.' . $index) ?? '1' }}" />
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="careertype{{ count(old('careertypes', [''])) }}"></tr>
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-md-12">
                            <button id="add_row" class="btn btn-default pull-left">+ Add Row</button>
                            <button id='delete_row' class="pull-right btn btn-danger">- Delete Row</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card team_level activation hidden">
                <div class="card-header">
                Tipe Aktivasi Team
                </div>

                <div class="card-body">
                    <table class="table" id="activationtypes_table">
                        <thead>
                            <tr>
                                <th>Items</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (old('activationtypes', ['']) as $index => $oldactivationtype)
                                <tr id="activationtype{{ $index }}">
                                    <td>
                                        <select name="activationtypes[]" class="form-control">
                                            <option value="">-- choose activationtype --</option>
                                            @foreach ($activationtypes as $activationtype)
                                                <option value="{{ $activationtype->id }}"{{ $oldactivationtype == $activationtype->id ? ' selected' : '' }}>
                                                    {{ $activationtype->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="amounts2[]" class="form-control" value="{{ old('amounts2.' . $index) ?? '1' }}" />
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="activationtype{{ count(old('activationtypes', [''])) }}"></tr>
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-md-12">
                            <button id="add_row2" class="btn btn-default pull-left">+ Add Row</button>
                            <button id='delete_row2' class="pull-right btn btn-danger">- Delete Row</button>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
  $(document).ready(function(){
    let row_number = {{ count(old('careertypes', [''])) }};
    $("#add_row").click(function(e){
      //alert('add_row'+row_number)
      e.preventDefault();
      let new_row_number = row_number - 1;
      $('#careertype' + row_number).html($('#careertype' + new_row_number).html()).find('td:first-child');
      $('#careertypes_table').append('<tr id="careertype' + (row_number + 1) + '"></tr>');
      row_number++;
    });

    $("#delete_row").click(function(e){
      e.preventDefault();
      if(row_number > 1){
        $("#careertype" + (row_number - 1)).html('');
        row_number--;
      }
    });

    let row_number2 = {{ count(old('activationtypes', [''])) }};
    $("#add_row2").click(function(e){
      //alert('add_row2'+row_number2)
      e.preventDefault();
      let new_row_number2 = row_number2 - 1;
      $('#activationtype' + row_number2).html($('#activationtype' + new_row_number2).html()).find('td:first-child');
      $('#activationtypes_table').append('<tr id="activationtype' + (row_number2 + 1) + '"></tr>');
      row_number2++;
    });

    $("#delete_row2").click(function(e){
      e.preventDefault();
      if(row_number2 > 1){
        $("#activationtype" + (row_number2 - 1)).html('');
        row_number2--;
      }
    });

    $('input[type="radio"]').click(function(){
        var inputValue = $(this).attr("value");
        var targetBox = $("." + inputValue);
        $(".team_level").not(targetBox).hide();
        $(targetBox).show();
    });

  });
</script>
@endsection
