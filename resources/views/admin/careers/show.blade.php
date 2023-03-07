@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.member.title') }}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.member.fields.name') }}
                    </th>
                    <td>
                        {{ $member->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.member.fields.address') }}
                    </th>
                    <td>
                        {!! $member->address !!}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('global.member.fields.email') }}
                    </th>
                    <td>
                        ${{ $member->email }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Jenjang Karir Saya :
                    </th>
                    <td>
                        {{ $members['level_name_checked'] }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@foreach ($careertypes as $careertype)
<div class="card">
    <div class="card-header">
        {{$careertype->name}}
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                    Aktivasi/Upgrade
                    </th>
                    <td>
                    {{$members['member']['activations']['name']}}//{{$careertype->activations->name}} @if($careertype->member_activation_status == 1)<input class="btn btn-primary" type="button" value="v">@endif @if($careertype->member_activation_status == 0)<input class="btn btn-danger" type="button" value="x">@endif
                    </td>
                </tr>
                <tr>
                    <th>
                    Repeat Order Minimal
                    </th>
                    <td>
                    {{$members['member_ro']}}//{{$careertype->ro_min_bv}} @if($careertype->member_ro_status == 1)<input class="btn btn-primary" type="button" value="v">@endif @if($careertype->member_ro_status == 0)<input class="btn btn-danger" type="button" value="x">@endif
                    </td>
                </tr>
                <tr>
                    <th>
                    Minimal Komisi
                    </th>
                    <td>
                    Bulan-1: {{$members['member_fee1'][0]['total']}}//{{$careertype->fee_min}}</br>
                    Bulan-2: {{$members['member_fee2'][0]['total']}}//{{$careertype->fee_min}}</br>
                    Bulan-3: {{$members['member_fee3'][0]['total']}}//{{$careertype->fee_min}}</br>
                    @if($careertype->member_fee_status == 1)<input class="btn btn-primary" type="button" value="v">@endif @if($careertype->member_fee_status == 0)<input class="btn btn-danger" type="button" value="x">@endif
                    </td>
                </tr>
                <tr>
                    <th>
                    Refrensi Langsung
                    </th>
                    <td>
                    {{$careertype->member_down}} {{$careertype->activationdownlines->name}}//{{$careertype->ref_downline_num}} {{$careertype->activationdownlines->name}} @if($careertype->member_down_status == 1)<input class="btn btn-primary" type="button" value="v">@endif @if($careertype->member_down_status == 0)<input class="btn btn-danger" type="button" value="x">@endif
                    </td>
                </tr>
                <tr>
                    <th>
                    Peringkat Team
                    </th>
                    <td>
                    @foreach ($careertype->team_levels as $careertype_team_level)
                    {{$careertype_team_level->amount}}  {{$careertype_team_level->name}}//{{$careertype_team_level->careertype_amount}}  {{$careertype_team_level->name}}</br>
                    @endforeach
                    @if($careertype->team_level_status == 1)<input class="btn btn-primary" type="button" value="v">@endif @if($careertype->team_level_status == 0)<input class="btn btn-danger" type="button" value="x">@endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endforeach

@endsection