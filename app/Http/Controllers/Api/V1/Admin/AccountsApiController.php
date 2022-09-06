<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Account;

class AccountsApiController extends Controller
{
    public function cash()
    {
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();

        return $accounts;
    }

    public function store(StoreAccountRequest $request)
    {
        return Account::create($request->all());
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        return $account->update($request->all());
    }

    public function show($id)
    {
        $account = Account::find($id);

        //Check if account found or not.
        if (is_null($account)) {
            $message = 'Product not found.';
            $status = false;
            $response = $this->response($status, $account, $message);
            return $response;
        }
        $message = 'Product retrieved successfully.';
        $status = true;

        //Call function for response data
        $response = $this->response($status, $account, $message);
        return $response;
    }

    public function destroy(Account $account)
    {
        return $account->delete();
    }

    /**
     * Response data
     *
     * @param $status
     * @param $account
     * @param $message
     * @return \Illuminate\Http\Response
     */
    public function response($status, $account, $message)
    {
        //Response data structure
        $return['success'] = $status;
        $return['data'] = $account;
        $return['message'] = $message;
        return $return;
    }
}
