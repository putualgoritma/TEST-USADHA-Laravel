<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Package;

class PackagesApiController extends Controller
{
    public function index()
    {
        $packages = Package::where('type', 'package')
        ->with('products')
        ->where('status', '=', 'show')
        ->get();

        return $packages;
    }

    public function packages($type)
    {
        $packages = Package::where('type', 'package')
        ->where('package_type', $type)
        ->where('status', '=', 'show')
        ->with('products')
        ->get();

        if (!empty($packages) && count($packages)>0) {
            return response()->json([
                'success' => true,
                'data' => $packages,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data is empty.',
            ], 401);
        }
    }

    public function store(StorePackageRequest $request)
    {
        return Package::create($request->all());
    }

    public function update(UpdatePackageRequest $request, Package $package)
    {
        return $package->update($request->all());
    }

    public function show($id)
    {
        $package = Package::find($id);

        //Check if package found or not.
        if (is_null($package)) {
            $message = 'Product not found.';
            $status = false;
            $response = $this->response($status, $package, $message);
            return $response;
        }
        $message = 'Product retrieved successfully.';
        $status = true;

        //Call function for response data
        $response = $this->response($status, $package, $message);
        return $response;
    }

    public function destroy(Package $package)
    {
        return $package->delete();
    }

    /**
     * Response data
     *
     * @param $status
     * @param $package
     * @param $message
     * @return \Illuminate\Http\Response
     */
    public function response($status, $package, $message)
    {
        //Response data structure
        $return['success'] = $status;
        $return['data'] = $package;
        $return['message'] = $message;
        return $return;
    }
}
