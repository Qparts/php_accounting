<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Traits\ApiResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    use ApiResponse;
    public function store(Request $request)
    {

        if(Auth::user()->can('create customer'))
        {
            $rules = [
                'contact.name' => 'required',
                'contact.organization' => 'required',
                'contact.email' =>'required',
                'contact.phone_number'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/'
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return $this->error($messages,409);
            }

            $objCustomer    = Auth::user();
            $creator        = User::find($objCustomer->creatorId());
            $total_customer = $objCustomer->countCustomers();
            $plan           = Plan::find($creator->plan);
            $default_language          = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            if($total_customer < $plan->max_customers || $plan->max_customers == -1)
            {
                $customer                  = new Customer();
                $customer->customer_id     = $request->contact['email'];
                $customer->name            = $request->contact['organization'];
                $customer->contact         = $request->contact['phone_number'];
                $customer->email           = $request->contact['email'];
                $customer->tax_number      =$request->contact['tax_number'];
                $customer->created_by      = Auth::user()->creatorId();
                $customer->billing_name    = $request->contact['billing_name'] ?? "Riyadh" ;
                $customer->billing_country = $request->contact['billing_country'] ?? "Saudi Arabia" ;
                $customer->billing_state   = $request->contact['billing_state'] ?? "Riyadh" ;
                $customer->billing_city    = $request->contact['billing_city'] ?? "Riyadh" ;
                $customer->billing_phone   = $request->contact['phone_number'];
                $customer->billing_zip     = $request->contact['billing_zip'] ?? 12211 ;
                $customer->billing_address = $request->contact['billing_address'] ?? "Riyadh" ;

                $customer->shipping_name    = $request->contact['shipping_name'] ?? "Riyadh" ;
                $customer->shipping_country = $request->contact['shipping_country'] ?? "Saudi Arabia" ;
                $customer->shipping_state   = $request->contact['shipping_state'] ?? "Riyadh" ;
                $customer->shipping_city    = $request->contact['shipping_city'] ?? "Riyadh" ;
                $customer->shipping_phone   = $request->contact['phone_number'];
                $customer->shipping_zip     = $request->contact['shipping_zip'] ?? 12211 ;
                $customer->shipping_address = $request->contact['shipping_address'] ?? "Riyadh" ;

                $customer->lang = !empty($default_language) ? $default_language->value : '';
                $customer->save();
            }
            else
            {
              return $this->error("something went wrong",409);
            }
            return response()->json(['customer'=>$customer]);
        }
        else
        {
            return $this->error("permission denied",409);
        }
    }

//    function customerNumber()
//    {
//        $latest = Customer::where('created_by', '=', Auth::user()->creatorId())->latest()->first();
//        if(!$latest)
//        {
//            return 1;
//        }
//
//        return $latest->customer_id + 1;
//    }


}
