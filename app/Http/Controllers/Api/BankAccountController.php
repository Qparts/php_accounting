<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    use ApiResponse;
    public function store(Request $request){
        $validator = \Validator::make(
            $request->all(), [
                'holder_name' => 'required',
                'bank_name' => 'required',
                'account_number' => 'required',
                'opening_balance' => 'required',
                'contact_number' => 'required'
            ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['error'=>$messages]);

        }

        $account = new BankAccount();
        $account->holder_name = $request->holder_name;
        $account->bank_name = $request->bank_name;
        $account->account_number = $request->account_number;
        $account->opening_balance = $request->opening_balance;
        $account->contact_number = $request->contact_number;
        $account->created_by = Auth::user()->id;
        $account->bank_address = $request->bank_address ?? " ";

        $account->save();
        return $this->success($account,"success",201);

    }

    public function list(){
        $accounts = BankAccount::where('created_by',Auth::user()->id)->get();
        if(!$accounts){
            return $this->error("No account related to this user found",404);
        }
        return $this->success($accounts,"success");
    }

    public function addMoneyToBankAccount(Request $request){
        $account = BankAccount::where('id',$request->account_id)->first();
        if(!$account){
            return $this->error("account not found",404);
        }
        $account->opening_balance = $account->opening_balance + $request->amount;
        $account->save();
        return $this->success($account,"success");
    }
}
