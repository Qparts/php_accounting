<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\BillProduct;
use App\Models\CustomField;
use App\Models\DebitNote;
use App\Models\InvoiceProduct;
use App\Models\Payment;
use App\Models\ProductService;
use App\Models\StockReport;
use App\Models\Transaction;
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
                    'bill.bill_id' => 'required',
                    'bill.issue_date' => 'required',
                    'bill.due_date' => 'required',

                    'bill.line_items' => 'required',
                    'bill.status' => 'required'
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error'=>$messages]);

            }
            $billExists = Bill::where('bill_id',$request->bill['bill_id'])->first();
            if($billExists){
                return response()->json(['error'=>"bill already exists"]);
            }
            $bill            = new Bill();
            $bill->bill_id   = $request->bill['bill_id'];
            $bill->vender_id = $request->bill['contact_id'];
            $bill->bill_date      = $request->bill['issue_date'];
            if($request->bill['status'] == "Approved"){
                $bill->status         = 4;
            }else{
                $bill->status         = 2;
            }
            $bill->due_date       = $request->bill['due_date'];
            $bill->category_id    = 1;
            $bill->send_date      = date('Y-m-d');
            $bill->order_number   = !empty($request->bill['reference']) ? $request->bill['reference'] : 0;
            $bill->created_by     = \Auth::user()->creatorId();
            $bill->save();
           // CustomField::saveData($bill, $request->customField);
            $products = $request->bill['line_items'];

            for($i = 0; $i < count($products); $i++)
            {
                $productBySKU = ProductService::where('sku',$products[$i]['product_id'])->first();
                $billProduct              = new BillProduct();
                $billProduct->bill_id     = $bill->id;
                $billProduct->product_id  = $productBySKU->id;
                $billProduct->quantity    = $products[$i]['quantity'];
                $billProduct->tax         = 1;
                $billProduct->discount    = $products[$i]['discount'];
                $billProduct->price       = $products[$i]['unit_price'];
                $billProduct->description = $products[$i]['description'];
                $billProduct->save();
                //inventory management (Quantity)
                Utility::total_quantity("plus",$products[$i]['quantity'],$productBySKU->id);

                //Product Stock Report
                $type='bill';
                $type_id = $bill->id;

                $description=$products[$i]['quantity'].'  '.__(' quantity purchase in bill').' '. \Auth::user()->billNumberFormat($bill->bill_id);
                Utility::addProductStock( $productBySKU->id,$products[$i]['quantity'],$type,$description,$type_id);

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
        $bill = Bill::where('created_by',Auth::user()->id)->where('bill_id',$id)->first();
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
            $billDue = Bill::where('bill_id', $bill_id)->first();
            if($request->bill['allocations_attributes'][0]['amount'] > $billDue->getDue())
            {
                return response()->json(['error'=>'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.']);
            }
            $bill               = Bill::where('bill_id', $bill_id)->first();
            $debit              = new DebitNote();
            $debit->bill        = $bill->id;
            $debit->vendor      = $bill->vender_id;
            $debit->date        = $request->bill['allocations_attributes'][0]['date'];
            $debit->amount      = $request->bill['allocations_attributes'][0]['amount'];
            $debit->description = "debit note";
            $debit->save();
            Utility::userBalance('vendor', $bill->vender_id, $request->bill['allocations_attributes'][0]['amount'], 'debit');

            return response()->json(['debit_note'=>$debit]);
        }
        else
        {
            return response()->json(['error'=>"permission denied"]);
        }
    }

    public function createPayment(Request $request)
    {
        if(\Auth::user()->can('create payment bill'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'bill_payment.date' => 'required',
                    'bill_payment.amount' => 'required',
                    'bill_payment.reference' => 'required',
                    'bill_payment.bill_id'=>'required'
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['error'=>$messages]);
            }

            $account = BankAccount::where('created_by',Auth::user()->id)->first();
            $bill = Bill::where('bill_id',$request->bill_payment['bill_id'])->first();
            $billPayment                 = new BillPayment();
            $billPayment->bill_id        = $bill->id;
            $billPayment->date           = $request->bill_payment['date'];
            $billPayment->amount         = $request->bill_payment['amount'];
            $billPayment->account_id     = $account->id;
            $billPayment->payment_method = 0;
            $billPayment->reference      = $request->bill_payment['reference'];
            $billPayment->description    = $request->bill_payment['description'] ?? NULL;

            $billPayment->save();
            $due   = $bill->getDue();

            if($bill->status == 0)
            {
                $bill->send_date = date('Y-m-d');
                $bill->save();
            }

            if($due <= 0)
            {
                $bill->status = 4;
                $bill->save();
            }
            else
            {
                $bill->status = 3;
                $bill->save();
            }
            $billPayment->user_id    = $bill->vender_id;
            $billPayment->user_type  = 'Vender';
            $billPayment->type       = 'Partial';
            $billPayment->created_by = \Auth::user()->id;
            $billPayment->payment_id = $billPayment->id;
            $billPayment->category   = 'Bill';
            $billPayment->account    = $account->id;
            Transaction::addTransaction($billPayment);

            // payment
            $payment                 = new Payment();
            $payment->date           = $request->bill_payment['date'];
            $payment->amount         = $request->bill_payment['amount'];
            $payment->account_id     = $account->id;
            $payment->vender_id      = $bill->vender_id;
            $payment->category_id    = $account->id;
            $payment->payment_method = 0;
            $payment->reference      = $request->bill_payment['reference'];
            $payment->description    = $request->bill_payment['description'] ?? NULL;
            $payment->created_by     = \Auth::user()->creatorId();
            $payment->save();
            //

            Utility::userBalance('vendor', $bill->vender_id, $request->bill_payment['amount'], 'debit');

            Utility::bankAccountBalance($account->id, $request->bill_payment['amount'], 'debit');


            return response()->json(['payment'=>$billPayment]);

            }

             return response()->json(['error'=>'permission denied.'],401);
        }




    public function createDebitNote(Request $request){
        if(\Auth::user()->can('create debit note'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'debit_note.issue_date' => 'required',
                    'debit_note.status' => 'required',
                    'debit_note.inventory_id' => 'required',
                    'debit_note.bill_id' => 'required',
                    'debit_note.line_items'=>'required',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['messages'=> $messages]);
            }
            $billDue = Bill::where('bill_id', $request->debit_note['bill_id'])->first();
           // dd($billDue);
            $amount = 0.0;
            // get all products related to invoice
            $productsInBill = BillProduct::where('bill_id',$billDue->id)->get(['product_id']);
            $productsInBillArray = [];
            foreach($productsInBill as $pr){
                array_push($productsInBillArray,$pr['product_id']);
            }
            foreach ($request->debit_note['line_items'] as $item){

                $productService = ProductService::where('sku',$item['product_id'])->first();

                if(in_array($productService->id,$productsInBillArray)){
                    $valueTobeSubtracted = $item['quantity']*$item['unit_price']; // number of items * price of each item
                    $amount=$amount + $valueTobeSubtracted;
                }

                $productService->quantity = $productService->quantity - $item['quantity'];
                $productService->save();
                $billProduct = BillProduct::where('bill_id',$billDue->id)->where('product_id',$productService->id)->first();
                $billProduct->quantity = $billProduct->quantity - $item['quantity'];
                $billProduct->price = $item['unit_price'];
                $billProduct->tax = 1;
                $billProduct->save();
            }

            if($request->amount > $billDue->getDue())
            {
                return response()->json(['error'=> 'Maximum ' . \Auth::user()->priceFormat($billDue->getDue()) . ' credit limit of this bill.']);
            }
            $bill               = Bill::where('bill_id', $request->debit_note['bill_id'])->first();
            $debit              = new DebitNote();
            $debit->bill        = $bill->id;
            $debit->vendor      = $bill->vender_id;
            $debit->date        = $request->debit_note['issue_date'];
            $debit->amount      = 0;
            $debit->description = "";
            $debit->save();
            Utility::userBalance('vendor', $bill->vender_id, $amount, 'debit');

            return response()->json(['debit'=> $debit]);
        }
        else
        {
            return response()->json(['error'=> 'Permission denied.']);
        }
    }


}
