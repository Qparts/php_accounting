<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductService;
use App\Models\StockReport;
use App\Models\Utility;
use App\Models\warehouse;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    use ApiResponse;
    public function store(Request $request)
{
    if(\Auth::user()->can('create warehouse'))
    {
        $validator = \Validator::make(
            $request->all(), [
                'inventory.name' => 'required',
                'inventory.name_ar' => 'required',
                'inventory.address' => 'required',
            ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return $this->error($messages,403);
        }
        $warehouse             = new warehouse();
        $warehouse->name       = $request->inventory['name'] . ' / ' . $request->inventory['name_ar'];
        $warehouse->address    = $request->inventory['address']['shipping_address'];
        $warehouse->city       = $request->inventory['address']['shipping_city'];
        $warehouse->city_zip   = $request->inventory['address']['shipping_zip'];
        $warehouse->created_by = \Auth::user()->creatorId();
        $warehouse->save();
        return $this->success($warehouse,201);
    }
    else
    {
        return $this->error("permission denied");
    }
}
}
