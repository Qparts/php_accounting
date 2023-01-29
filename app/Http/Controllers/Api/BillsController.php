<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\CustomField;
use App\Models\DebitNote;
use App\Models\Utility;
use App\Models\Vender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillsController extends Controller
{
    public function store(Request $request){
        if(\Auth::user()->can('create bill'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'bill.contact_id' => 'required',
                    'bill.issue_date' => 'required',
                    'bill.due_date' => 'required',
                    'bill.category_id' => 'required',
                    'bill.line_items' => 'required',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error'=>$messages]);

            }
            $bill            = new Bill();
            $bill->bill_id   = $this->billNumber();
            $bill->vender_id = $request->bill['contact_id'];
            $bill->bill_date      = $request->bill['issue_date'];
            $bill->status         = 0;
            $bill->due_date       = $request->bill['due_date'];
            $bill->category_id    = $request->bill['category_id'];
            $bill->order_number   = !empty($request->bill['reference']) ? $request->bill['reference'] : 0;
            $bill->created_by     = \Auth::user()->creatorId();
            $bill->save();
           // CustomField::saveData($bill, $request->customField);
            $products = $request->bill['line_items'];

            for($i = 0; $i < count($products); $i++)
            {
                $billProduct              = new BillProduct();
                $billProduct->bill_id     = $bill->id;
                $billProduct->product_id  = $products[$i]['product_id'];
                $billProduct->quantity    = $products[$i]['quantity'];
                $billProduct->tax         = $products[$i]['tax_percent'];
                $billProduct->discount    = $products[$i]['discount'];
                $billProduct->price       = $products[$i]['unit_price'];
                $billProduct->description = $products[$i]['description'];
               // $billProduct->save();
                //inventory management (Quantity)
                Utility::total_quantity("plus",$products[$i]['quantity'],$products[$i]['product_id']);

                //Product Stock Report
                $type='bill';
                $type_id = $bill->id;

                $description=$products[$i]['quantity'].'  '.__(' quantity purchase in bill').' '. \Auth::user()->billNumberFormat($bill->bill_id);
                Utility::addProductStock( $products[$i]['product_id'],$products[$i]['quantity'],$type,$description,$type_id);

            }
            return response()->json(['bill'=>$bill]);

        }
        else
        {
            return response()->json(['error'=>"permission denied"]);
        }
    }

    public function listBills(){
        $bills = Bill::where('created_by',Auth::user()->id)->get();
        if(!$bills){
            return response()->json(['message'=>"no bills found"]);

        }
        return response()->json(['bills'=>$bills]);

    }

    function billNumber()
    {
        $latest = Bill::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->bill_id + 1;
    }

    public function getBill($id){
        $bill = Bill::where('created_by',Auth::user()->id)->where('id',$id)->first();
        if(!$bill){
            return response()->json(['message'=>"no bill found"]);
        }
        return response()->json(['bill'=>$bill]);
    }

    public function debitNote(Request $request,$id){
        if(\Auth::user()->can('create debit note'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'bill'=>[
                        'allocations_attributes.amount' => 'required',
                        'allocations_attributes.date' => 'required'
                    ]
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error'=>$messages]);
            }

            $bill_id = $id;
            $billDue = Bill::where('id', $bill_id)->first();

            if($request->amount > $billDue->getDue())
            {
                return response()->json(['error'=>'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.']);
            }
            $bill               = Bill::where('id', $bill_id)->first();
            $debit              = new DebitNote();
            $debit->bill        = $bill_id;
            $debit->vendor      = $bill->vender_id;
            $debit->date        = $request->bill['allocations_attributes']['date'];
            $debit->amount      = $request->bill['allocations_attributes']['amount'];
            $debit->description = "debit note";
            $debit->save();
            Utility::userBalance('vendor', $bill->vender_id, $request->amount, 'debit');

            return response()->json(['debit_note'=>$debit]);
        }
        else
        {
            return response()->json(['error'=>"permission denied"]);
        }
    }
}
