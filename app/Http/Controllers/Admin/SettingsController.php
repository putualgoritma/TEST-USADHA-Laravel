<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('setting_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $env_arr=array();
        $env_arr['admin_acc_trsf'] = env('ADMIN_ACC_TRSF');
        $env_arr['member_activ'] = env('MEMBER_ACTIV');

        return view('admin.settings.index', compact('env_arr'));
    }

    public function update(Request $request)
    {
        abort_unless(\Gate::allows('setting_show'), 403);
        if ($request->has('admin_acc_trsf')) {              
            $this->envUpdate('ADMIN_ACC_TRSF', 1);
        }else{
            $this->envUpdate('ADMIN_ACC_TRSF', 0);
        }
        if ($request->has('member_activ')) {              
            $this->envUpdate('MEMBER_ACTIV', 1);
        }else{
            $this->envUpdate('MEMBER_ACTIV', 0);
        }
        $message = 'Update Setting Sudah Berhasil!';
        return back()->withError($message)->withInput();

    }

    public function envUpdate($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                $key . '=' . env($key), $key . '=' . $value, file_get_contents($path)
            ));
        }
    }

}
