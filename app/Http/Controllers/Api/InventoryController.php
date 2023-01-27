<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductService;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
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
    public function show(){
        $warehouses = warehouse::where('created_by',Auth::user()->id)->get();
        return response()->json(['inventories'=>$warehouses]);
    }
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
        return response()->json(['inventory'=>$warehouse]);
    }
    else
    {
        return $this->error("permission denied");
    }
}

    public function purchaseProductsForInventory(Request $request)
    {

        if(\Auth::user()->can('create purchase'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'inventory_adjustment.vendor_id' => 'required',
                    'inventory_adjustment.inventory_id' => 'required',
                    'inventory_adjustment.date' => 'required',
                    'inventory_adjustment.category_id' => 'required',
                    'inventory_adjustment.line_items' => 'required',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return $this->error($messages);
            }

            $purchase                 = new Purchase();
            $purchase->purchase_id    = $this->purchaseNumber();
            $purchase->vender_id      = $request->inventory_adjustment['vendor_id'];
            $purchase->warehouse_id      = $request->inventory_adjustment['inventory_id'];
            $purchase->purchase_date  = $request->inventory_adjustment['date'];
            $purchase->purchase_number   = !empty($request->purchase_number) ? $request->purchase_number : 0;
            $purchase->status         =  0;
            $purchase->category_id    = $request->inventory_adjustment['category_id'];
            $purchase->created_by     = \Auth::user()->creatorId();
            $purchase->save();

            $products = $request->inventory_adjustment['line_items'];

            for($i = 0; $i < count($products); $i++)
            {
                $purchaseProduct              = new PurchaseProduct();
                $purchaseProduct->purchase_id     = $purchase->id;
                $purchaseProduct->product_id  = $products[$i]['product_id'];
                $purchaseProduct->quantity    = $products[$i]['actual_quantity'];
                $purchaseProduct->tax         = $products[$i]['tax'];

                $purchaseProduct->discount    = 0.0;
                $purchaseProduct->price       = $products[$i]['rate'];
                $purchaseProduct->description = $request->inventory_adjustment['description'];
                $purchaseProduct->save();
                //inventory management (Quantity)
                Utility::total_quantity('plus',$products[$i]['actual_quantity'],$products[$i]['product_id']);

                //Product Stock Report
                $type='purchase';
                $type_id = $purchase->id;
                $description=$products[$i]['actual_quantity'].'  '.__(' quantity add in purchase').' '. \Auth::user()->purchaseNumberFormat($purchase->purchase_id);
                Utility::addProductStock( $products[$i]['product_id'],$products[$i]['actual_quantity'],$type,$description,$type_id);

                //Warehouse Stock Report
                if(isset($products[$i]['product_id']))
                {
                    Utility::addWarehouseStock( $products[$i]['product_id'],$products[$i]['actual_quantity'],$request->inventory_adjustment['inventory_id']);
                }

            }
            return response()->json(['purchase'=>$purchase]);
        }
        else
        {
            return $this->error("permission denied");
        }
    }
    function purchaseNumber()
    {
        $latest = Purchase::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->purchase_id + 1;
    }
}
