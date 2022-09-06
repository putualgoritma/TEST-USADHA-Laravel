@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        OTP {{ trans('global.withdraw.title_singular') }}
    </div>

    <div class="card-body">
        @if($errors->has('msg'))
            <div class="alert alert-danger" role="alert">
                {{$errors->first('msg') }}
            </div>
        @endif
        NONONONOOO {{ $reference_no }}
        <form action="{{ route("admin.withdraw.otpApproved") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="id" name="id" value="{{ $withdraw->id }}">
            <input type="hidden" id="acc_pay" name="acc_pay" value="{{ $withdraw->acc_pay }}">
            <input type="hidden" name="reference_no" value="{{ $reference_no }}">
            <div class="form-group">
                <label for="OTP">OTP</label>
                <input type="text" id="OTP" class="form-control" required name="OTP">
            </div>
            <div class="form-group px-2">
                <input class="btn btn-danger" type="submit" value="Approve">
            </div>
        </form>
    </div>
</div>
@endsection
