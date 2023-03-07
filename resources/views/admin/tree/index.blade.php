@extends('layouts.tree')
@section('content')

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if (session('message'))
    <div class="alert alert-danger">{{ session('message') }}</div>
@endif

<div class="content bg-white">
    <h1></h1>

    <div class="row">
        <div class="col-lg-12">

                <div class="row" style="height:800px;
                overflow: auto;">
                <div class="tree d-flex justify-content-center" style="height:100px;
              ">

        <ul class="tree d-flex justify-content-center" style="
        width:1000px;
     ">
<li>
@if($slot_prev_x>=0)
                        <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_prev_x,'slot_y'=>$slot_prev_y]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/up-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
                        @if(!$slot_arr[0][0]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[0][0]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[0][0]['data']->id]) }}">
                                @endif
                                <div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
    @if($slot_arr[0][0]['data'])
    <img class="card-img-top" src="{{ ($slot_arr[0][0]['data']['activations']->id > 1) && ($slot_arr[0][0]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
        <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[0][0]['data']->name}}</p>
         <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[0][0]['data']->code}}</p>
         <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[0][0]['data']['activations']->name}}</p>
         @endif
      </div></a>
    <ul>
   {{-- kiri --}}
            <li>
            @if(!$slot_arr[1][0]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[1][0]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[1][0]['data']->id]) }}">
                                @endif
                    <div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                @if($slot_arr[1][0]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[1][0]['data']['activations']->id > 1) && ($slot_arr[1][0]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[1][0]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[1][0]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[1][0]['data']['activations']->name}}</p>
                     @endif
                  </div>
                  </a>
                <ul>

                    <li>
                    @if(!$slot_arr[2][0]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[2][0]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[2][0]['data']->id]) }}">
                                @endif
                            <div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">

                        @if($slot_arr[2][0]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[2][0]['data']['activations']->id > 1) && ($slot_arr[2][0]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][0]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][0]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][0]['data']['activations']->name}}</p>
                     @endif

                          </div>
                          </a>
              <ul>
        <li>
        @if(!$slot_arr[3][0]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[3][0]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][0]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                        @if($slot_arr[3][0]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][0]['data']['activations']->id > 1) && ($slot_arr[3][0]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][0]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][0]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][0]['data']['activations']->name}}</p>
                     @endif

                          </div></a>
                        @if($slot_arr[3][0]['data'])
                        <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][0]['x'],'slot_y'=>$slot_arr[3][0]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
                        </li>
                    <li>
                    @if(!$slot_arr[3][1]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[3][1]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][1]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                            @if($slot_arr[3][1]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][1]['data']['activations']->id > 1) && ($slot_arr[3][1]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][1]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][1]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][1]['data']['activations']->name}}</p>
                     @endif

                          </div></a>
                          @if($slot_arr[3][1]['data'])
                          <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][1]['x'],'slot_y'=>$slot_arr[3][1]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
                    </li>

                    </li>
                </ul>

            </li>


                <li>
                @if(!$slot_arr[2][1]['data'])
                <a href="#">
                        @endif
                        @if($slot_arr[2][1]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[2][1]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                                @if($slot_arr[2][1]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[2][1]['data']['activations']->id > 1) && ($slot_arr[2][1]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][1]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][1]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][1]['data']['activations']->name}}</p>
                     @endif

                                  </div></a>
                      <ul>
                <li>
                @if(!$slot_arr[3][2]['data'])
                <a href="#">
                        @endif
                        @if($slot_arr[3][2]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][2]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                                @if($slot_arr[3][2]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][2]['data']['activations']->id > 1) && ($slot_arr[3][2]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][2]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][2]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][2]['data']['activations']->name}}</p>
                     @endif

                                  </div></a>
                                  @if($slot_arr[3][2]['data'])
                                  <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][2]['x'],'slot_y'=>$slot_arr[3][2]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
                                </li>
                            <li>
                            @if(!$slot_arr[3][3]['data'])
                            <a href="#">
                        @endif
                        @if($slot_arr[3][3]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][3]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                                @if($slot_arr[3][3]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][3]['data']['activations']->id > 1) && ($slot_arr[3][3]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][3]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][3]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][3]['data']['activations']->name}}</p>
                     @endif

                                  </div></a>
                                  @if($slot_arr[3][3]['data'])
                                  <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][3]['x'],'slot_y'=>$slot_arr[3][3]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
                            </li>

                        </ul>

                    </li>

                </ul>
            </li>
{{-- kanan --}}
<li>
@if(!$slot_arr[1][1]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[1][1]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[1][1]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
    @if($slot_arr[1][1]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[1][1]['data']['activations']->id > 1) && ($slot_arr[1][1]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[1][1]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[1][1]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[1][1]['data']['activations']->name}}</p>
                     @endif

      </div></a>
    <ul>

        <li>
        @if(!$slot_arr[2][2]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[2][2]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[2][2]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
            @if($slot_arr[2][2]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[2][2]['data']['activations']->id > 1) && ($slot_arr[2][2]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][2]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][2]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][2]['data']['activations']->name}}</p>
                     @endif

              </div></a>
  <ul>
<li>
@if(!$slot_arr[3][4]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[3][4]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][4]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
            @if($slot_arr[3][4]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][4]['data']['activations']->id > 1) && ($slot_arr[3][4]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][4]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][4]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][4]['data']['activations']->name}}</p>
                     @endif

              </div></a>
              @if($slot_arr[3][4]['data'])
              <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][4]['x'],'slot_y'=>$slot_arr[3][4]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
            </li>
        <li>
        @if(!$slot_arr[3][5]['data'])
                        <a href="#">
                        @endif
                        @if($slot_arr[3][5]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][5]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
            @if($slot_arr[3][5]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][5]['data']['activations']->id > 1) && ($slot_arr[3][5]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][5]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][5]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][5]['data']['activations']->name}}</p>
                     @endif

              </div></a>
              @if($slot_arr[3][5]['data'])
              <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][5]['x'],'slot_y'=>$slot_arr[3][5]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
        </li>

        </li>
    </ul>

</li>


    <li>
    @if(!$slot_arr[2][3]['data'])
    <a href="#">
                        @endif
                        @if($slot_arr[2][3]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[2][3]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                    @if($slot_arr[2][3]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[2][3]['data']['activations']->id > 1) && ($slot_arr[2][3]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][3]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][3]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[2][3]['data']['activations']->name}}</p>
                     @endif

                      </div></a>
          <ul>
    <li>
    @if(!$slot_arr[3][6]['data'])
    <a href="#">
                        @endif
                        @if($slot_arr[3][6]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][6]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                    @if($slot_arr[3][6]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][6]['data']['activations']->id > 1) && ($slot_arr[3][6]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][6]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][6]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][6]['data']['activations']->name}}</p>
                     @endif

                      </div></a>
                      @if($slot_arr[3][6]['data'])
                      <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][6]['x'],'slot_y'=>$slot_arr[3][6]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
                    </li>
                <li>
                @if(!$slot_arr[3][7]['data'])
                <a href="#">
                        @endif
                        @if($slot_arr[3][7]['data'])
                        <a href="javascript:;" data-toggle="modal" class="mediumButton" data-target="#mediumModal"
                                data-attr="{{ route('admin.trees.modal', ['id'=>$slot_arr[3][7]['data']->id]) }}">
                                @endif<div class="card" style="width: 5rem; 	border: 0px solid #ccc; height:5.5rem;">
                    @if($slot_arr[3][7]['data'])
                <img class="card-img-top" src="{{ ($slot_arr[3][7]['data']['activations']->id > 1) && ($slot_arr[3][7]['data']->status == 'active') ? asset('images/user.png') : asset('images/user-off.png') }}" alt="Card image cap">
                    <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][7]['data']->name}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][7]['data']->code}}</p>
                     <p class="text-info" style="font-size:7px; font-color: '#0000FF'; margin:0px; ">{{$slot_arr[3][7]['data']['activations']->name}}</p>
                     @endif

                      </div></a>
                      @if($slot_arr[3][7]['data'])
                      <div>
                        <a href="{{ route('admin.trees.view',['slot_x'=>$slot_arr[3][7]['x'],'slot_y'=>$slot_arr[3][7]['y']]) }}"><div class="card" style="width: 5rem; 	border: 0px solid #fff; height:3.5rem;">
                        <img class="card-img-top" src="{{ asset('images/down-arrow.png') }}" alt="Down Arrow">
                        </div></a>
                        </div>
                        @endif
                </li>

            </ul>

        </li>

    </ul>
</li>
</ul>
</li>



        </ul>
    </div>
</div>

</div>
</div>
</div>

<!-- medium modal -->
    <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="mediumBody">
                    <div>
                        <!-- the result to be displayed apply here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>

        // display a modal (medium modal)
        $(document).on('click', '.mediumButton', function(event) {
            event.preventDefault();

            // $('#mediumModal').modal("show");
            // $('#mediumBody').html('<div>Haloha...</div>').show();

            let href = $(this).attr('data-attr');
            $.ajax({
                url: href,
                beforeSend: function() {
                    $('#loader').show();
                },
                // return the result
                success: function(result) {
                    $('#mediumModal').modal("show");
                    $('#mediumBody').html(result).show();
                },
                complete: function() {
                    $('#loader').hide();
                },
                error: function(jqXHR, testStatus, error) {
                    console.log(error);
                    alert("Page " + href + " cannot open. Error:" + error);
                    $('#loader').hide();
                },
                timeout: 8000
            })
        });

    </script>
@parent

@endsection
