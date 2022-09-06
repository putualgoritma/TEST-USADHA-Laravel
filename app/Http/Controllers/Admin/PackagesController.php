<?php

namespace App\Http\Controllers\Admin;

use App\Package;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Gate;
use App\Http\Requests\MassDestroyPackageRequest;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Activation;
use Yajra\DataTables\Facades\DataTables;

class PackagesController extends Controller
{
    
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('package_access'), 403);

        // ajax
        if ($request->ajax()) {

            $query = Package::where('type', 'package')
            ->FilterStatus()
            ->with('products');
            
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'package_show';
                $editGate = 'package_edit';
                $deleteGate = 'package_delete';
                $crudRoutePart = 'packages';

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

            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });

            $table->editColumn('price', function ($row) {
                return $row->price ? $row->price : "";
            });

            $table->editColumn('bv', function ($row) {
                return $row->bv ? $row->bv : "";
            });

            $table->editColumn('product', function ($row) {
                $product_list = '<ul>';
                foreach ($row->products as $key => $item) {
                    $product_list .= '<li>' . $item->name . " (" . $item->pivot->quantity . " x " . number_format($item->price, 2) . ")" . '</li>';
                }
                $product_list .= '</ul>';
                return $product_list;
            });

            $table->rawColumns(['actions', 'placeholder', 'product']);

            // $table->addIndexColumn();
            return $table->make(true);
        }       
        //def view
        $packages = Package::where('type', 'package')
            ->FilterStatus()
            ->with('products');
        return view('admin.packages.index', compact('packages'));
    }
    
    // public function index()
    // {
    //     abort_if(Gate::denies('package_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    //     //$packages = Package::with('products')->get();
    //     $packages = Package::where('type', 'package')
    //     ->where('status', 'show')
    //     ->with('products')
    //     ->get();

    //     return view('admin.packages.index', compact('packages'));
    // }

    public function create()
    {
        abort_if(Gate::denies('package_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        //$products = Product::all();
        $products = Product::where('type', 'single')->where('status', 'show')->get();
        $activations = Activation::all();

        return view('admin.packages.create', compact('products','activations'));
    }

    public function store(StorePackageRequest $request)
    {
        //init
        $products = $request->input('products', []);
        $quantities = $request->input('quantities', []);
        $cogs_total =0;
        $bv =0;
        //set cogs
        for ($product=0; $product < count($products); $product++) {
            $product_row = Product::find($products[$product]);
            $cogs_total += $quantities[$product] * $product_row->cogs;   
            $bv += $quantities[$product] * $product_row->bv;          
        }
        $data=array_merge($request->all(), ['type' => 'package','cogs' => $cogs_total,'bv' => $bv]);
        $package=Package::create($data);
        
        //store to package_product
        for ($product=0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $package->products()->attach($products[$product], ['quantity' => $quantities[$product]]);
            }
        }
        return redirect()->route('admin.packages.index');
        
    }

    public function edit(Package $package)
    {
        abort_if(Gate::denies('package_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        //$products = Product::all();
        $products = Product::where('type', 'single')->where('status', 'show')->get();
        $activations = Activation::all();

        $package->load('products');

        return view('admin.packages.edit', compact('products', 'package', 'activations'));
    }

    public function update(UpdatePackageRequest $request, Package $package)
    {
        //init
        $products = $request->input('products', []);
        $quantities = $request->input('quantities', []);
        $cogs_total =0;
        $bv =0;
        //set cogs
        for ($product=0; $product < count($products); $product++) {
            $product_row = Product::find($products[$product]);
            $cogs_total += $quantities[$product] * $product_row->cogs;
            $bv += $quantities[$product] * $product_row->bv;            
        }
        $data=array_merge($request->all(), ['type' => 'package','cogs' => $cogs_total,'bv' => $bv]);
        
        $img_path="/images/products";
        $basepath=str_replace("laravel-admin","public_html/admin",\base_path());
        if ($request->file('img') != null) {
            $resource = $request->file('img');
            //$img_name = $resource->getClientOriginalName();
            $name=strtolower($request->input('name'));
            $name=str_replace(" ","-",$name);
            $img_name = $img_path."/".$name."-".$package->id."-01.".$resource->getClientOriginalExtension();
            try {
                //unlink old
                $data = array_merge($request->all(), ['img' => $img_name]);
                $resource->move($basepath . $img_path, $img_name);
            } catch (QueryException $exception) {
                return back()->withError('File is too large!')->withInput();
            }
        }
        
        $package->update($data);

        $package->products()->detach();
        for ($product=0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $package->products()->attach($products[$product], ['quantity' => $quantities[$product]]);
            }
        }

        return redirect()->route('admin.packages.index');
    }

    public function show(Package $package)
    {
        abort_if(Gate::denies('package_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $package->load('products');

        return view('admin.packages.show', compact('package'));
    }

    public function destroy(Package $package)
    {
        abort_if(Gate::denies('package_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $package->delete();

        return back();
    }

    public function massDestroy(MassDestroyPackageRequest $request)
    {
        Package::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
