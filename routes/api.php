<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::group(['prefix' => 'v1', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin'], function () {
//     Route::apiResource('permissions', 'PermissionsApiController');

//     Route::apiResource('roles', 'RolesApiController');

//     Route::apiResource('users', 'UsersApiController');

//     Route::apiResource('products', 'ProductsApiController');
// });

Route::group(['prefix' => 'open', 'namespace' => 'Api\V1\Admin'], function () {
    Route::post('/login', 'CustomersApiController@login');
    Route::post('/loginagent', 'CustomersApiController@loginagent');
    Route::post('/register', 'CustomersApiController@register');
    Route::post('/register-agent', 'CustomersApiController@registerAgent');
    Route::get('/logout', 'CustomersApiController@logout')->middleware('auth:api');
    Route::post('/reset', 'CustomersApiController@resetUser');
    Route::post('/user-block', 'CustomersApiController@userBlock');
    Route::get('/products', 'ProductsApiController@index');
    Route::get('product/{id}', 'ProductsApiController@show');
    Route::get('/products-member', 'ProductsApiController@indexMember');
    Route::get('/products-agent', 'ProductsApiController@indexAgent');
    Route::get('/agents', 'CustomersApiController@agentsOpen');
    Route::get('/test', 'CustomersApiController@test');
    Route::get('/location', 'CustomersApiController@location');
    Route::get('/login-switch', 'CustomersApiController@loginSwitch');
});

Route::group(['prefix' => 'close', 'namespace' => 'Api\V1\Admin', 'middleware' => 'auth:api'], function () {
    Route::get('/products', 'ProductsApiController@index');
    Route::get('product/{id}', 'ProductsApiController@show');
    Route::post('topup', 'TopupsApiController@store');
    Route::post('activate', 'CustomersApiController@activate');
    Route::get('/packages', 'PackagesApiController@index');
    Route::get('package/{id}', 'PackagesApiController@show');
    Route::get('/accountcashs', 'AccountsApiController@cash');
    Route::get('account/{id}', 'AccountsApiController@show');
    Route::get('/agents', 'CustomersApiController@agents');
    Route::get('agent/{id}', 'CustomersApiController@agentshow');
    Route::get('balance/{id}', 'TopupsApiController@balance');
    Route::post('order', 'OrdersApiController@store');
    Route::get('history/{id}', 'TopupsApiController@history');
    Route::get('history-fee/{id}', 'TopupsApiController@historyFee');
    Route::post('member-show', 'CustomersApiController@membershow');
    Route::post('member-showid', 'CustomersApiController@membershowid');
    Route::post('transfer', 'TopupsApiController@transfer');
    Route::get('members', 'CustomersApiController@members');
    Route::post('members-pagination', 'CustomersApiController@membersPagination');
    Route::post('order-agent', 'OrdersApiController@storeAgent');
    Route::post('/update-profile', 'CustomersApiController@updateprofile');
    Route::get('history-order/{id}', 'OrdersApiController@history');
    Route::get('history-order-agent/{id}', 'OrdersApiController@historyAgent');
    Route::get('product-stock/{id}', 'ProductsApiController@stockMember');
    Route::get('/packages/{id}', 'PackagesApiController@packages');
    Route::post('activate-agent', 'CustomersApiController@activateAgent');
    Route::post('withdraw', 'TopupsApiController@withdraw');
    Route::get('downline/{id}', 'CustomersApiController@downline');
    Route::get('downline-agent/{id}', 'CustomersApiController@downlineAgent');
    Route::post('/register-downline', 'CustomersApiController@registerDownline');
    Route::get('order-agent-process/{id}', 'OrdersApiController@orderAgentProcess');
    Route::get('order-cancel/{id}', 'OrdersApiController@orderCancel');
    Route::get('delivery-agent-update/{id}', 'OrdersApiController@deliveryAgentUpdate');
    Route::get('delivery-member-update/{id}', 'OrdersApiController@deliveryMemberUpdate');
    Route::get('/products-member', 'ProductsApiController@indexMember');
    Route::get('/products-agent', 'ProductsApiController@indexAgent');   
    Route::post('/logs', 'CustomersApiController@logs'); 
    Route::post('/logs-unread', 'CustomersApiController@logsUnread');
    Route::get('logs-update-status/{id}', 'CustomersApiController@logsUpdate');
    Route::post('/upload-img/{id}', 'CustomersApiController@upImg');
    
    //tree
    Route::get('member-tree', 'CustomersApiController@downlineTree');
    // Route::get('downline-test/{id}', 'CustomersApiController@downlineTest');

    Route::post('upgrade', 'CustomersApiController@upgrade');
    Route::get('/products-member-upgrade/{id}', 'ProductsApiController@indexMemberUpgrade');
    Route::post('/topup/map', 'TopupsApiController@topupMAP');

    Route::get('province-city', 'OngkirApiController@provinceCity');
    Route::get('province-city-test', 'OngkirApiController@provinceCityTest');

    Route::get('/points', 'OrdersApiController@points');
    Route::get('/points-total', 'OrdersApiController@pointsTotal');

    Route::post('convert', 'TopupsApiController@convert');

    Route::get('/activation-type', 'ProductsApiController@activationType');

    Route::post('/register-downline-custpackage', 'CustomersApiController@registerDownlineCustPackage');

    Route::post('activate-custpackage', 'CustomersApiController@activateCustPackage');

    Route::post('upgrade-custpackage', 'CustomersApiController@upgradeCustPackage');

    Route::get('actv-balance/{id}', 'TopupsApiController@actvBalance');

    Route::post('martregisters-join', 'MartregisterApiController@join');
    Route::post('martregisters-registerdownline', 'MartregisterApiController@registerdownline');
    Route::post('martregisters-upgrade', 'MartregisterApiController@upgrade');

    //career
    Route::get('/careertypes-list', 'CareertypesApiController@lists');

    Route::get('/agent-list', 'CustomersApiController@agentlist');
    Route::post('transfer-stock', 'OrdersApiController@transferStock');
    Route::get('members-hu', 'CustomersApiController@membersHu');
    Route::get('/products-member-package', 'ProductsApiController@indexMemberPackage');
    Route::get('/activation-type-detail', 'ProductsApiController@activationTypeDetail');
    Route::get('slot-tree', 'CustomersApiController@slotTree');
    Route::get('slot-empty', 'CustomersApiController@slotEmpty');
    Route::get('slot-if-lr', 'CustomersApiController@slotIfLR');
    Route::get('slot-list-up', 'CustomersApiController@slotListUp');
    Route::get('group-lr-amount', 'CustomersApiController@groupLRAmount');
    Route::get('net-info', 'CustomersApiController@net_info');
    Route::get('pairing-info', 'CustomersApiController@pairing_info');
    Route::get('status-list-up', 'CustomersApiController@statusListUp');
    Route::get('pairing-test', 'CustomersApiController@testPairing');

    //automaintain
    Route::post('automaintain', 'OrdersApiController@automaintain');
    Route::get('auto-maintain', 'CustomersApiController@getAutoMaintain');

    //test
    Route::get('test/{id}', 'OrdersApiController@test');
});
