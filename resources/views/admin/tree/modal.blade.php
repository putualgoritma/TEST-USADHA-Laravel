@extends('layouts.page')
@section('content')

<div class="card">
    <div class="card-header">
        
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('global.member.fields.name') }}
                    </th>
                    <td>
                    {{ $user->code }} - {{ $user->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Member Type
                    </th>
                    <td>
                        {{ $user->activations->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Refferal/Sponsor
                    </th>
                    <td>
                    {{ $user->refferal->code }} - {{ $user->refferal->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Jenjang Karir
                    </th>
                    <td>
                        {{ $careertype_name }}
                    </td>
                </tr>   
                <tr>
                    <th>
                        Total Level
                    </th>
                    <td>
                        {{ $net_info_arr['level_total'] }} level
                    </td>
                </tr> 
                <tr>
                    <th>
                    Member (Kiri)
                    </th>
                    <td>
                        {{ number_format($net_info_arr['left_total'],0,',','.') }} member
                    </td>
                </tr>  
                <tr>
                    <th>
                    Member (Kanan)
                    </th>
                    <td>
                        {{ number_format($net_info_arr['right_total'],0,',','.') }} member
                    </td>
                </tr>   
                <tr>
                    <th>
                    Member (Generasi 1)
                    </th>
                    <td>
                        {{ $net_info_arr['ref_total'] }} member
                    </td>
                </tr>    
                <tr>
                    <th>
                    Pairing Tunggu (Kanan)
                    </th>
                    <td>
                        {{ number_format($pairing_info_arr['bv_pairing_r'],0,',','.') }} bv
                    </td>
                </tr>    
                <tr>
                    <th>
                    Pairing Tunggu (Kiri)
                    </th>
                    <td>
                        {{ number_format($pairing_info_arr['bv_pairing_l'],0,',','.') }} bv
                    </td>
                </tr>
                <tr>
                    <th>
                    Total Pairing
                    </th>
                    <td>
                        {{ $pairing_info_arr['bv_queue_c_count'] }} kali
                    </td>
                </tr>
                <tr>
                    <th>
                    Total Nilai
                    </th>
                    <td>
                        Rp. {{ number_format($pairing_info_arr['bv_queue_c'],0,',','.')  }}
                    </td>
                </tr>
                <tr>
                    <th>
                    Total Hari Ini
                    </th>
                    <td>
                        Rp. {{ number_format($pairing_info_arr['get_bv_daily_queue'],0,',','.')  }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection