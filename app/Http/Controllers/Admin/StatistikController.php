<?php

namespace App\Http\Controllers\Admin;

use App\Chart;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Order;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index(Request $request)
    {

        if ($request->month == 'all' || $request->month == null || $request->month == '') {
            $month = null;
        } else {
            $month = $request->month;
        }

        if ($request->year != '') {
            $year = $request->year;
        } else {
            $year = date('Y');
        }

        $data = [];
        $dataCount = [];
        $label = [];

        if ($month != null) {
            $statistic_sale_nominal = Order::selectRaw('DATE(created_at) as created_at_tanggal, sum(total) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where(function ($query) {
                    $query->where('type', 'sale')
                        ->orWhere('type', 'activation_agent');
                })
                ->where('status', 'approved')
                ->groupBy('created_at_tanggal')
                ->get();

            $statistic_sale_count = Order::selectRaw('DATE(created_at) as created_at_tanggal, count(id) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where(function ($query) {
                    $query->where('type', 'sale')
                        ->orWhere('type', 'activation_agent');
                })
                ->where('status', 'approved')
                ->groupBy('created_at_tanggal')
                ->get();

            for ($i = 0; $i <= count($statistic_sale_nominal); $i++) {
                $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
            }

            for ($i = 1; $i <= 31; $i++) {
                array_push($label, $i);
                array_push($data, 0);
                array_push($dataCount, 0);
            }

            foreach ($label as $key => $value) {
                foreach ($statistic_sale_nominal as $key => $item) {
                    $index = explode('-', $item->created_at_tanggal)[2];
                    $data[$index - 1] = $item->total;
                }

                foreach ($statistic_sale_count as $key => $item) {
                    $index = explode('-', $item->created_at_tanggal)[2];
                    $dataCount[$index - 1] = $item->total;
                }
            }

        } else {
            $statistic_sale_nominal = Order::selectRaw('created_at, sum(total) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where(function ($query) {
                    $query->where('type', 'sale')
                        ->orWhere('type', 'activation_agent');
                })
                ->where('status', 'approved')
                ->groupBy('year', 'month')
                ->get();

            $statistic_sale_count = Order::selectRaw('created_at, count(id) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where(function ($query) {
                    $query->where('type', 'sale')
                        ->orWhere('type', 'activation_agent');
                })
                ->where('status', 'approved')
                ->groupBy('year', 'month')
                ->get();

            for ($i = 0; $i <= count($statistic_sale_nominal); $i++) {
                $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
            }

            for ($m = 1; $m <= 12; ++$m) {
                array_push($label, date('F', mktime(0, 0, 0, $m, 1)));
                array_push($data, 0);
                array_push($dataCount, 0);
            }

            foreach ($label as $key => $value) {
                foreach ($statistic_sale_nominal as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index = date_format($item->created_at, 'm');
                    if ($value == $isiBulan) {
                        $data[$index - 1] = $item->total;
                        break;
                    }
                }

                foreach ($statistic_sale_count as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index = date_format($item->created_at, 'm');
                    if ($value == $isiBulan) {
                        $dataCount[$index - 1] = $item->total;
                        break;
                    }
                }
            }
        }

        $chart = new Chart();
        $chart->labels = $label;
        $chart->dataset = $data;
        $chart->colours = $colours;

        $chartCount = new Chart();
        $chartCount->labels = $label;
        $chartCount->dataset = $dataCount;
        $chartCount->colours = $colours;

        return view('admin.statistik.index', compact('chart', 'chartCount'));
    }

    public function product(Request $request)
    {

        if ($request->month == 'all' || $request->month == null || $request->month == '') {
            $month = null;
        } else {
            $month = $request->month;
        }

        if ($request->year != '') {
            $year = $request->year;
        } else {
            $year = date('Y');
        }

        $data = [];
        $label = [];
        $data2 = [];
        $label2 = [];
        for ($i = 0; $i < 10; $i++) {
            $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
        }
        if ($month != '') {
            $statistic_sale_product = Order::selectRaw('DATE(orders.created_at) as created_at,products.name as product_name, sum(product_order_details.quantity) as total')
                ->join('product_order_details', 'orders.id', '=', 'product_order_details.orders_id')
                ->join('products', 'products.id', '=', 'product_order_details.products_id')
                ->whereYear('orders.created_at', '=', $year)
                ->whereMonth('orders.created_at', '=', $month)
                ->where(function ($query) {
                    $query->where('orders.type', 'sale')
                        ->orWhere('orders.type', 'activation_agent');
                })
                ->where('orders.status', 'approved')
                ->where('product_order_details.type', 'C')
                ->groupBy('product_order_details.products_id')
                ->orderBy('total', 'desc')
            // ->take(10)
                ->get();

            $statistic_sale_package = Order::selectRaw('DATE(orders.created_at) as created_at,products.name as product_name, sum(order_product.quantity) as total')
                ->join('order_product', 'orders.id', '=', 'order_product.order_id')
                ->join('products', 'products.id', '=', 'order_product.product_id')
                ->whereYear('orders.created_at', '=', $year)
                ->whereMonth('orders.created_at', '=', $month)
                ->where(function ($query) {
                    $query->where('orders.type', 'sale')
                        ->orWhere('orders.type', 'activation_agent');
                })
                ->where('orders.status', 'approved')
                ->where('products.type', 'package')
                ->groupBy('order_product.product_id')
                ->orderBy('total', 'desc')
            // ->take(10)
                ->get();

            foreach ($statistic_sale_product as $key => $item) {
                array_push($label, $item->product_name);
                array_push($data, $item->total);
            }
            foreach ($statistic_sale_package as $key => $item) {
                array_push($label2, $item->product_name);
                array_push($data2, $item->total);
            }
        } else {
            $statistic_sale_product = Order::selectRaw('Month(orders.created_at) as month,Year(orders.created_at) as year,products.name as product_name, sum(product_order_details.quantity) as total')
                ->join('product_order_details', 'orders.id', '=', 'product_order_details.orders_id')
                ->join('products', 'products.id', '=', 'product_order_details.products_id')
                ->whereYear('orders.created_at', '=', $year)
                ->where(function ($query) {
                    $query->where('orders.type', 'sale')
                        ->orWhere('orders.type', 'activation_agent');
                })
                ->where('orders.status', 'approved')
                ->where('product_order_details.type', 'C')
                ->groupBy('product_order_details.products_id')
            // ->take(10)
                ->get();
            $statistic_sale_package = Order::selectRaw('Month(orders.created_at) as month,Year(orders.created_at) as year,products.name as product_name, sum(order_product.quantity) as total')
                ->join('order_product', 'orders.id', '=', 'order_product.order_id')
                ->join('products', 'products.id', '=', 'order_product.product_id')
                ->whereYear('orders.created_at', '=', $year)
                ->where(function ($query) {
                    $query->where('orders.type', 'sale')
                        ->orWhere('orders.type', 'activation_agent');
                })
                ->where('orders.status', 'approved')
                ->where('products.type', 'package')
                ->groupBy('order_product.product_id')
            // ->take(10)
                ->get();
            foreach ($statistic_sale_product as $key => $item) {
                array_push($label, $item->product_name);
                array_push($data, $item->total);
            }
            foreach ($statistic_sale_package as $key => $item) {
                array_push($label2, $item->product_name);
                array_push($data2, $item->total);
            }
        }

        $chart = new Chart();
        $chart->labels = $label;
        $chart->dataset = $data;
        $chart->colours = $colours;

        $chart2 = new Chart();
        $chart2->labels = $label2;
        $chart2->dataset = $data2;
        $chart2->colours = $colours;

        // dd($statistic_sale_product);
        return view('admin.statistik.statistikproduct', compact('chart', 'chart2'));
    }

    public function member(Request $request)
    {

        if ($request->month == 'all' || $request->month == null || $request->month == '') {
            $month = null;
        } else {
            $month = $request->month;
        }

        if ($request->year != '') {
            $year = $request->year;
        } else {
            $year = date('Y');
        }

        $data = [];
        $dataCount = [];
        $dataCount2 = [];
        $dataCount3 = [];
        $dataCount4 = [];
        $label = [];

        if ($month != null) {

            $statistic_sale_count = Customer::selectRaw('DATE(created_at) as created_at_tanggal, count(id) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '1')
                ->groupBy('created_at_tanggal')
                ->get();

            $statistic_sale_count2 = Customer::selectRaw('DATE(created_at) as created_at_tanggal, count(id) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '2')
                ->groupBy('created_at_tanggal')
                ->get();

            $statistic_sale_count3 = Customer::selectRaw('DATE(created_at) as created_at_tanggal, count(id) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '3')
                ->groupBy('created_at_tanggal')
                ->get();

            $statistic_sale_count4 = Customer::selectRaw('DATE(created_at) as created_at_tanggal, count(id) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '4')
                ->groupBy('created_at_tanggal')
                ->get();

            for ($i = 0; $i <= count($statistic_sale_count); $i++) {
                $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
            }

            for ($i = 1; $i <= 31; $i++) {
                array_push($label, $i);
                array_push($data, 0);
                array_push($dataCount, 0);
                array_push($dataCount2, 0);
                array_push($dataCount3, 0);
                array_push($dataCount4, 0);
            }

            foreach ($label as $key => $value) {

                foreach ($statistic_sale_count as $key => $item) {
                    $index = explode('-', $item->created_at_tanggal)[2];
                    $dataCount[$index - 1] = $item->total;
                }
                foreach ($statistic_sale_count2 as $key => $item) {
                    $index = explode('-', $item->created_at_tanggal)[2];
                    $dataCount2[$index - 1] = $item->total;
                }
                foreach ($statistic_sale_count3 as $key => $item) {
                    $index = explode('-', $item->created_at_tanggal)[2];
                    $dataCount3[$index - 1] = $item->total;
                }
                foreach ($statistic_sale_count4 as $key => $item) {
                    $index = explode('-', $item->created_at_tanggal)[2];
                    $dataCount4[$index - 1] = $item->total;
                }
            }

        } else {

            $statistic_sale_count = Customer::selectRaw('created_at, count(id) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '1')
                ->groupBy('year', 'month')
                ->get();

            $statistic_sale_count2 = Customer::selectRaw('created_at, count(id) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '2')
                ->groupBy('year', 'month')
                ->get();

            $statistic_sale_count3 = Customer::selectRaw('created_at, count(id) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '3')
                ->groupBy('year', 'month')
                ->get();

            $statistic_sale_count4 = Customer::selectRaw('created_at, count(id) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where('type', 'member')
                ->where('status', 'active')
                ->where('activation_type_id', '4')
                ->groupBy('year', 'month')
                ->get();

            for ($i = 0; $i <= count($statistic_sale_count); $i++) {
                $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
            }

            for ($m = 1; $m <= 12; ++$m) {
                array_push($label, date('F', mktime(0, 0, 0, $m, 1)));
                array_push($data, 0);
                array_push($dataCount, 0);
                array_push($dataCount2, 0);
                array_push($dataCount3, 0);
                array_push($dataCount4, 0);
            }

            foreach ($label as $key => $value) {
                foreach ($statistic_sale_count as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index = date_format($item->created_at, 'm');
                    if ($value == $isiBulan) {
                        $dataCount[$index - 1] = $item->total;
                        break;
                    }
                }
                foreach ($statistic_sale_count2 as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index = date_format($item->created_at, 'm');
                    if ($value == $isiBulan) {
                        $dataCount2[$index - 1] = $item->total;
                        break;
                    }
                }
                foreach ($statistic_sale_count3 as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index = date_format($item->created_at, 'm');
                    if ($value == $isiBulan) {
                        $dataCount3[$index - 1] = $item->total;
                        break;
                    }
                }
                foreach ($statistic_sale_count4 as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index = date_format($item->created_at, 'm');
                    if ($value == $isiBulan) {
                        $dataCount4[$index - 1] = $item->total;
                        break;
                    }
                }
            }
        }

        $chart = new Chart();
        $chart->labels = $label;
        $chart->dataset = $data;
        $chart->colours = $colours;

        $chartCount = new Chart();
        $chartCount->labels = $label;
        $chartCount->dataset = $dataCount;
        $chartCount->colours = $colours;

        $chartCount2 = new Chart();
        $chartCount2->labels = $label;
        $chartCount2->dataset = $dataCount2;
        $chartCount2->colours = $colours;

        $chartCount3 = new Chart();
        $chartCount3->labels = $label;
        $chartCount3->dataset = $dataCount3;
        $chartCount3->colours = $colours;

        $chartCount4 = new Chart();
        $chartCount4->labels = $label;
        $chartCount4->dataset = $dataCount4;
        $chartCount4->colours = $colours;

        return view('admin.statistik.statistikmember', compact('chart', 'chartCount', 'chartCount2', 'chartCount3', 'chartCount4'));
    }
}
