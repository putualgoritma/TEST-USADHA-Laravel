<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DB;

class ResetController extends Controller
{
    public function index()
    {
        DB::table('product_order_details')->delete();
        DB::table('order_product')->delete();
        DB::table('order_points')->delete();
        DB::table('orders')->delete();
        DB::table('ledger_entries')->delete();
        DB::table('ledgers')->delete();
        DB::table('customers')->where('def', 0)->delete();
        return 'Reset Data Berhasil.';
    }

    public function resetall()
    {
        DB::table('product_order_details')->delete();
        DB::table('order_product')->delete();
        DB::table('order_points')->delete();
        DB::table('orders')->delete();
        DB::table('ledger_entries')->delete();
        DB::table('ledgers')->delete();
        DB::table('package_product')->delete();
        DB::table('products')->delete();
        DB::table('customers')->where('def', 0)->delete();
        $img_path=\base_path() . "/images/products";
        $images = glob($img_path."/*.jpg");
        foreach ($images as $image) {
            @unlink($image);
        }
        return 'Reset Data Berhasil.';
    }

}
