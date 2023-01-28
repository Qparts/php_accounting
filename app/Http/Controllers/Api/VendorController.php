<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use App\Models\Vender;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class VendorController extends Controller
{
    use ApiResponse;

    public function listVendors(){
        $vendors =  Vender::where('created_by',Auth::user()->id)->get();
        return response()->json(['vendors'=>$vendors]);
    }

    public function store(Request $request)
    {
        if(Auth::user()->can('create vender'))
        {
            $rules = [
            'contact.name'=>'required',
            'contact.organization'=>'required',
            'contact.email'=>'required',
            'contact.phone_number'=>'required',
            'contact.tax_number'=>'required',
            ];
            $validator = \Validator::make($request->all(), $rules);
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return  $this->error($messages,"409");
            }

            $objVendor    = Auth::user();
            $creator      = User::find($objVendor->creatorId());
            $total_vendor = $objVendor->countVenders();
            $plan         = Plan::find($creator->plan);

            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            if($total_vendor < $plan->max_venders || $plan->max_venders == -1)
            {
                $vender                   = new Vender();
                $vender->vender_id        = $this->venderNumber();
                $vender->name             = $request->contact['name'];
                $vender->contact          = $request->contact['phone_number'];
                $vender->email            = $request->contact['email'];
                $vender->tax_number      =$request->contact['tax_number'];
                $vender->created_by       = Auth::user()->creatorId();
                $vender->billing_name     = $request->billing_name;
                $vender->billing_country  = $request->billing_country;
                $vender->billing_state    = $request->billing_state;
                $vender->billing_city     = $request->billing_city;
                $vender->billing_phone    = $request->billing_phone;
                $vender->billing_zip      = $request->billing_zip;
                $vender->billing_address  = $request->billing_address;
                $vender->shipping_name    = $request->shipping_name;
                $vender->shipping_country = $request->shipping_country;
                $vender->shipping_state   = $request->shipping_state;
                $vender->shipping_city    = $request->shipping_city;
                $vender->shipping_phone   = $request->shipping_phone;
                $vender->shipping_zip     = $request->shipping_zip;
                $vender->shipping_address = $request->shipping_address;

                //addresses
                $vender->billing_name    = $request->contact['billing_name'] ?? "Riyadh" ;
                $vender->billing_country = $request->contact['billing_country'] ?? "Saudi Arabia" ;
                $vender->billing_state   = $request->contact['billing_state'] ?? "Riyadh" ;
                $vender->billing_city    = $request->contact['billing_city'] ?? "Riyadh" ;
                $vender->billing_phone   = $request->contact['phone_number'];
                $vender->billing_zip     = $request->contact['billing_zip'] ?? 12211 ;
                $vender->billing_address = $request->contact['billing_address'] ?? "Riyadh" ;

                $vender->shipping_name    = $request->contact['shipping_name'] ?? "Riyadh" ;
                $vender->shipping_country = $request->contact['shipping_country'] ?? "Saudi Arabia" ;
                $vender->shipping_state   = $request->contact['shipping_state'] ?? "Riyadh" ;
                $vender->shipping_city    = $request->contact['shipping_city'] ?? "Riyadh" ;
                $vender->shipping_phone   = $request->contact['phone_number'];
                $vender->shipping_zip     = $request->contact['shipping_zip'] ?? 12211 ;
                $vender->shipping_address = $request->contact['shipping_address'] ?? "Riyadh" ;
                $vender->lang             = !empty($default_language) ? $default_language->value : '';
                $vender->save();
            }
            else
            {
            return $this->error("something went wrong",409);
            }
            $role_r = Role::where('name', '=', 'vender')->firstOrFail();
            $vender->assignRole($role_r); //Assigning role to user
            return response()->json(['vendor'=>$vender]);
        }
        else
        {
            return $this->error("you don't have permission",401);
        }
    }

    public function getVendor($id){
        $vendor = Vender::where('id',$id)->where('created_by',Auth::user()->id)->first();
        return response()->json(['vendor'=>$vendor]);
    }
    function venderNumber()
    {
        $latest = Vender::where('created_by', '=', Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->vender_id + 1;
    }

}
