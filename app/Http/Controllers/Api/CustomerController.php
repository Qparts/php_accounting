<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Traits\ApiResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Transaction;
use App\Models\Utility;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use ApiResponse;
    public function store(Request $request)
    {

        if(Auth::user()->can('create customer'))
        {
            $rules = [
                'name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => [
                    'required',
                    Rule::unique('customers')->where(function ($query) {
                        return $query->where('created_by', Auth::user()->id);
                    })
                ],
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('customer.index')->with('error', $messages->first());
            }

            $objCustomer    = Auth::user();
            $creator        = User::find($objCustomer->creatorId());
            $total_customer = $objCustomer->countCustomers();
            $plan           = Plan::find($creator->plan);
            $default_language          = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            if($total_customer < $plan->max_customers || $plan->max_customers == -1)
            {
                $customer                  = new Customer();
                $customer->customer_id     = $this->customerNumber();
                $customer->name            = $request->name;
                $customer->contact         = $request->contact;
                $customer->email           = $request->email;
                $customer->tax_number      =$request->tax_number;
                $customer->created_by      = Auth::user()->creatorId();
                $customer->billing_name    = $request->billing_name;
                $customer->billing_country = $request->billing_country;
                $customer->billing_state   = $request->billing_state;
                $customer->billing_city    = $request->billing_city;
                $customer->billing_phone   = $request->billing_phone;
                $customer->billing_zip     = $request->billing_zip;
                $customer->billing_address = $request->billing_address;

                $customer->shipping_name    = $request->shipping_name;
                $customer->shipping_country = $request->shipping_country;
                $customer->shipping_state   = $request->shipping_state;
                $customer->shipping_city    = $request->shipping_city;
                $customer->shipping_phone   = $request->shipping_phone;
                $customer->shipping_zip     = $request->shipping_zip;
                $customer->shipping_address = $request->shipping_address;

                $customer->lang = !empty($default_language) ? $default_language->value : '';
                $customer->save();
            }
            else
            {
              return $this->error("something went wrong",409);
            }
            return $this->success($customer,"success");
        }
        else
        {
            return $this->error("permission denied",409);
        }
    }

    function customerNumber()
    {
        $latest = Customer::where('created_by', '=', Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->customer_id + 1;
    }
}
