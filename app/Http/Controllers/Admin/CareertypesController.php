<?php

namespace App\Http\Controllers\Admin;

use App\ActivationType;
use App\Careertype;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCareertypeRequest;
use App\Http\Requests\StoreCareertypeRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TraitModel;

class CareertypesController extends Controller
{
    use TraitModel;   
    
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('careertype_access'), 403);

        // ajax
        if ($request->ajax()) {

            $query = Careertype::selectRaw("careertypes.*")
                ->with('activations')
                ->with('activationdownlines');

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'careertype_show';
                $editGate = 'careertype_edit';
                $deleteGate = 'careertype_delete';
                $crudRoutePart = 'careertypes';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('activation_type_id', function ($row) {
                return $row->activations->name ? $row->activations->name : "";
            });

            $table->editColumn('ro_min_bv', function ($row) {
                return $row->ro_min_bv ? $row->ro_min_bv : "";
            });

            $table->editColumn('fee_min', function ($row) {
                return $row->fee_min ? $row->fee_min : "";
            });

            $table->editColumn('fee_max', function ($row) {
                return $row->fee_max ? $row->fee_max : "";
            });

            $table->editColumn('ref_downline_id', function ($row) {
                return $row->activationdownlines->name ? $row->ref_downline_num . ' - ' . $row->activationdownlines->name : "";
            });

            $table->rawColumns(['actions', 'placeholder', 'careertype']);

            // $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $careertypes = Careertype::selectRaw("careertypes.*")
            ->with('activations')
            ->with('activationdownlines');

        return view('admin.careertypes.index', compact('careertypes'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('careertype_create'), 403);
        $activationtypes = ActivationType::get();
        $careertypes = Careertype::get();

        return view('admin.careertypes.create', compact('activationtypes', 'careertypes'));
    }

    public function store(StoreCareertypeRequest $request)
    {
        abort_unless(\Gate::allows('careertype_create'), 403);

        //init
        $data = array_merge($request->all());
        $careertype = Careertype::create($data);

        if ($request->team_level == 'career') {
            $careertypes = $request->input('careertypes', []);
            $amounts = $request->input('amounts', []);
            //store to cogs_careertypes
            for ($careertypeinc = 0; $careertypeinc < count($careertypes); $careertypeinc++) {
                if ($careertypes[$careertypeinc] != '') {
                    $careertype->careertypes()->attach($careertypes[$careertypeinc], ['amount' => $amounts[$careertypeinc]]);
                }
            }}
        if ($request->team_level == 'activation') {
            $activationtypes = $request->input('activationtypes', []);
            $amounts2 = $request->input('amounts2', []);
            //store to cogs_activationtypes
            for ($activationtype = 0; $activationtype < count($activationtypes); $activationtype++) {
                if ($activationtypes[$activationtype] != '') {
                    $careertype->activationtypes()->attach($activationtypes[$activationtype], ['amount' => $amounts2[$activationtype]]);
                }
            }}

        return redirect()->route('admin.careertypes.index');
    }

    public function edit(Careertype $careertype)
    {
        abort_unless(\Gate::allows('careertype_edit'), 403);

        $activationtypes = ActivationType::get();
        $careertypes = Careertype::get();

        $careertype->load('activationtypes');
        $careertype->load('careertypes');

        return view('admin.careertypes.edit', compact('careertype', 'activationtypes', 'careertypes'));
    }

    public function update(StoreCareertypeRequest $request, Careertype $careertype)
    {
        abort_unless(\Gate::allows('careertype_edit'), 403);

        //init
        $data = array_merge($request->all());
        $careertype->update($data);

        if ($request->team_level == 'career') {
            $careertypes = $request->input('careertypes', []);
            $amounts = $request->input('amounts', []);
            //detach
            $careertype->careertypes()->detach();
            //store to cogs_careertypes
            for ($careertypeinc = 0; $careertypeinc < count($careertypes); $careertypeinc++) {
                if ($careertypes[$careertypeinc] != '') {
                    $careertype->careertypes()->attach($careertypes[$careertypeinc], ['amount' => $amounts[$careertypeinc]]);
                }
            }}
        if ($request->team_level == 'activation') {
            $activationtypes = $request->input('activationtypes', []);
            $amounts2 = $request->input('amounts2', []);
            //detach
            $careertype->activationtypes()->detach();
            //store to cogs_activationtypes
            for ($activationtype = 0; $activationtype < count($activationtypes); $activationtype++) {
                if ($activationtypes[$activationtype] != '') {
                    $careertype->activationtypes()->attach($activationtypes[$activationtype], ['amount' => $amounts2[$activationtype]]);
                }
            }}

        return redirect()->route('admin.careertypes.index');

    }

    public function show(Careertype $careertype)
    {
        abort_unless(\Gate::allows('careertype_show'), 403);

        return view('admin.careertypes.show', compact('careertype'));
    }

    public function destroy(Careertype $careertype)
    {
        abort_unless(\Gate::allows('careertype_delete'), 403);

        $careertype->delete();

        return back();
    }

    public function massDestroy(MassDestroyCareertypeRequest $request)
    {
        Careertype::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
