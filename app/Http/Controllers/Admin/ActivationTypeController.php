<?php

namespace App\Http\Controllers\Admin;

use App\ActivationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyActivationTypeRequest;
use App\Http\Requests\StoreActivationTypeRequest;
use App\Http\Requests\UpdateActivationTypeRequest;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class ActivationTypeController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('activation_type_access'), 403);

        if ($request->ajax()) {
            $query = ActivationType::get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'activation_type_show';
                $editGate = 'activation_type_edit';
                $deleteGate = 'activation_type_delete';
                $crudRoutePart = 'activation-type';

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

            $table->editColumn('type', function ($row) {
                return $row->type ? $row->type : "";
            });

            $table->editColumn('bv_min', function ($row) {
                return $row->bv_min ? $row->bv_min : "";
            });

            $table->editColumn('bv_max', function ($row) {
                return $row->bv_max ? $row->bv_max : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        // $activation_type = ActivationType::where('type', '!=', 'member')
        // ->where('def', '=', '0')
        // ->get();

        $activation_type = ActivationType::get();

        return view('admin.activationtype.index', compact('activation_type'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('activation_type_create'), 403);

        return view('admin.activationtype.create');
    }

    public function store(StoreActivationTypeRequest $request)
    {
        abort_unless(\Gate::allows('activation_type_create'), 403);

        $activation_type = ActivationType::create($request->all());

        return redirect()->route('admin.activation-type.index');
    }

    public function edit(ActivationType $activation_type)
    {
        abort_unless(\Gate::allows('activation_type_edit'), 403);

        return view('admin.activationtype.edit', compact('activation_type'));
    }

    public function update(UpdateActivationTypeRequest $request, ActivationType $activation_type)
    {
        abort_unless(\Gate::allows('activation_type_edit'), 403);

        $activation_type->update($request->all());
        return redirect()->route('admin.activation-type.index');
    }

    public function show(ActivationType $activation_type)
    {
        abort_unless(\Gate::allows('activation_type_show'), 403);

        return view('admin.activationtype.show', compact('activation_type'));
    }

    public function destroy(ActivationType $activation_type)
    {
        abort_unless(\Gate::allows('activation_type_delete'), 403);

        return back()->withError('Gagal Delete, Member Active!');
    }

    public function massDestroy(MassDestroyActivationTypeRequest $request)
    {
        // ActivationType::whereIn('id', request('ids'))->delete();

        // return response(null, 204);
        return back()->withError('Gagal Delete, Member Active!');
    }
}
