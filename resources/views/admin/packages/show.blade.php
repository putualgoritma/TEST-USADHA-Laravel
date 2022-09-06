@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('global.package.title') }}
    </div>

    <div class="card-body">
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('global.package.fields.name') }}
                        </th>
                        <td>
                            {{ $package->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('global.package.fields.description') }}
                        </th>
                        <td>
                            {!! $package->description !!}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('global.package.fields.price') }}
                        </th>
                        <td>
                            ${{ $package->price }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Products
                        </th>
                        <td>
                            @foreach($package->products as $id => $products)
                                <span class="label label-info label-many">{{ $products->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>
            <a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>


    </div>
</div>
@endsection