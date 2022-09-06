<?php

namespace App\Http\Controllers\Admin;

use App\NetworkFee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyNetworkFeeRequest;
use App\Http\Requests\StoreNetworkFeeRequest;
use App\Http\Requests\UpdateNetworkFeeRequest;
use App\Traits\TraitModel;
use App\Activation;

class NetworkFeesController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('networkfee_access'), 403);

        $networkfees = NetworkFee::where('status','show')->orderBy("ordernum", "asc")->get();

        return view('admin.networkfee.index', compact('networkfees'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('networkfee_create'), 403);

        $activations = Activation::all();

        return view('admin.networkfee.create', compact('activations'));
    }

    public function store(StoreNetworkFeeRequest $request)
    {
        abort_unless(\Gate::allows('networkfee_create'), 403);

        $networkfee=NetworkFee::create($request->all());

        return redirect()->route('admin.fees.index');
    }

    public function edit($id,NetworkFee $networkfee)
    {
        abort_unless(\Gate::allows('networkfee_edit'), 403);

        $activations = Activation::all();
        $networkfee = NetworkFee::find($id);

        //return $networkfee;
        return view('admin.networkfee.edit', compact('networkfee','activations'));
        //return Redirect()->Route('admin.fees.index');
    }

    public function update($id, UpdateNetworkFeeRequest $request, NetworkFee $networkfee)
    {
        abort_unless(\Gate::allows('networkfee_edit'), 403);

        $networkfee = NetworkFee::find($id);
        //return $networkfee;
        $networkfee->update($request->all());
        return Redirect()->Route('admin.fees.index');
    }

    public function show(NetworkFee $networkfee)
    {
        abort_unless(\Gate::allows('networkfee_show'), 403);

        //return view('admin.networkfee.show', compact('networkfee'));
        return Redirect()->Route('admin.fees.index');
    }

    public function destroy(NetworkFee $customer)
    {
        abort_unless(\Gate::allows('networkfee_delete'), 403);

        // $customer->delete();
        // return back();
        return Redirect()->Route('admin.fees.index');
    }

    public function massDestroy(MassDestroyNetworkFeeRequest $request)
    {
        //NetworkFee::whereIn('id', request('ids'))->delete();

        //return response(null, 204);
        return Redirect()->Route('admin.fees.index');
    }
}
