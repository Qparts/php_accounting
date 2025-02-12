<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Revenue;
use App\Models\StockReport;
use App\Models\Transaction;
use App\Models\Utility;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function createInvoice(Request $request){
        if(Auth::user()->can('create invoice'))
        {
            //creation of invoice
            $validator = \Validator::make(
                 $request->all(), [
                    'invoice.contact_id' => 'required',
                    'invoice.reference' => 'required',
                    'invoice.issue_date' => 'required',
                    'invoice.due_date' => 'required',
                    'invoice.status' => 'required',
                   // 'invoice.inventory_id' => 'required',
                    'invoice.line_items' => 'required'
                ]

            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return $this->error($messages,409);
            }

            $invoiceExists = Invoice::where('invoice_id',$request->invoice['reference'])->first();
            if($invoiceExists){
                return response()->json(['error'=>"invoice already exists"],409);
            }
            $invoice                 = new Invoice();
            $invoice->invoice_id     = $request->invoice['reference'];
            $invoice->customer_id    = $request->invoice['contact_id'];
            if($request->invoice['status']=="Approved"){
                $invoice->status         = 4;
            }else{
                $invoice->status         = 2;
            }

            $invoice->issue_date     = $request->invoice['issue_date'];
            $invoice->send_date     = $request->invoice['issue_date'];
            $invoice->due_date       = $request->invoice['due_date'];
            $invoice->category_id    = $request->invoice['inventory_id'];
            $invoice->ref_number     = $request->invoice['reference'];
            $invoice->created_by     = Auth::user()->creatorId();
            $invoice->save();
            CustomField::saveData($invoice, $request->custom_fields);
            $products = $request->invoice['line_items'];
            for($i = 0; $i < count($products); $i++)
            {
                $productBySKU = ProductService::where('sku',$products[$i]['product_id'])->first();
                $invoiceProduct              = new InvoiceProduct();
                $invoiceProduct->invoice_id  = $invoice->id;
                $invoiceProduct->product_id  = $productBySKU->id;
                $invoiceProduct->quantity    = $products[$i]['quantity'];
                //$invoiceProduct->tax         = $products[$i]['tax_percent'] ?? 1;
                $invoiceProduct->tax         = 1;
                $invoiceProduct->discount    = $products[$i]['discount'];
                $invoiceProduct->price       = $products[$i]['unit_price'];
                $invoiceProduct->description = $products[$i]['description'] ?? " " ;
                $invoiceProduct->save();
                //inventory management (Quantity)
                Utility::total_quantity('minus',$invoiceProduct->quantity,$invoiceProduct->product_id);

            }

            $type='invoice';
            $type_id = $invoice->id;
            StockReport::where('type','=','invoice')->where('type_id' ,'=', $invoice->id)->delete();
            $description=$invoiceProduct->quantity.'  '.__(' quantity sold in invoice').' '. Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            Utility::addProductStock( $invoiceProduct->product_id,$invoiceProduct->quantity,$type,$description,$type_id);
            return response()->json(['invoice'=>$invoice]);

        }
        else
        {
            return $this->error("something went wrong",409);
        }
    }

    public function customerPayment(Request $request){
        if(\Auth::user()->can('create revenue'))
        {

            $validator = \Validator::make(
                $request->all(), [
                'invoice_payment.reference'=>'required',
                'invoice_payment.date'=>'required',
                'invoice_payment.amount'=>'required',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return $this->error($messages,409);
            }
            $invoice     = Invoice::where('invoice_id',$request->invoice_payment['reference'])->first();
            if(!$invoice){
                return $this->error("invoice not found",404);

            }

            $bankAccount = BankAccount::where('created_by',Auth::user()->id)->first();

            $revenue                 = new Revenue();
            $revenue->date           = $request->invoice_payment['date'];
            $revenue->amount         = $request->invoice_payment['amount'];
            $revenue->account_id     = $bankAccount->id;
            $revenue->customer_id    = $invoice->customer_id;
            $revenue->category_id    = $request->invoice_payment['category_id']?? 1;
            $revenue->payment_method = 0;
            $revenue->reference      = $request->invoice_payment['reference'];

            $revenue->created_by     = \Auth::user()->creatorId();
            $revenue->save();
        //    $category            = ProductServiceCategory::where('id', $request->invoice_payment['category_id'])->first();


            $revenue->payment_id = $revenue->id;
            $revenue->type       = 'Revenue';
           // $revenue->category   = $category->name;
            $revenue->category   = 1; //TODO to be removed
            $revenue->user_id    = $revenue->customer_id;
            $revenue->user_type  = 'Customer';
            $revenue->account    = $bankAccount->id;
            Transaction::addTransaction($revenue);

            $customer         = Customer::where('customer_id', $invoice->customer_id)->first();
            $payment          = new InvoicePayment();
            $payment->date    = date('Y-m-d');
            $payment->amount  = $request->invoice_payment['amount'];
            $payment->invoice_id = $invoice->id;
            $payment->reference = $request->invoice_payment['reference'];
            $payment->save();

            $credit              = new CreditNote();
            //   $credit->invoice     = $request->credit_note['invoice_id'];
            $credit->invoice     = $invoice->id;
            $credit->customer    = $invoice->customer_id;
            $credit->date        = $request->invoice_payment['date'];
            $credit->amount      = $request->invoice_payment['amount'];
            $credit->description = $description ?? " ";
            $credit->save();


            if(!empty($customer))
            {
                Utility::userBalance('customer', $invoice->customer_id, $revenue->amount, 'credit');
            }

            Utility::bankAccountBalance( $bankAccount->id, $revenue->amount, 'credit');

            return $this->success($revenue,"success");
        }
        else
        {
            return $this->error("you don't have permission",401);
        }
    }


    public function createCreditNote(Request $request){
        if(\Auth::user()->can('create credit note'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'credit_note.contact_id' => 'required',
                    'credit_note.issue_date' => 'required',
                    'credit_note.status' => 'required',
                   // 'credit_note.inventory_id' => 'required',
                    'credit_note.invoice_id' => 'required',
                    'credit_note.line_items'=>'required',
                   // 'credit_note.description'=>'required'
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['messages'=> $messages]);
            }

            $invoiceDue = Invoice::where('invoice_id', $request->credit_note['invoice_id'])->with('payments')->first();


            $amount = 0.0;
           // $description = "";
            // get all products related to invoice
            $productsInInvoice = InvoiceProduct::where('invoice_id',$invoiceDue->id)->get(['product_id']);
            $productsInInvoiceArray = [];
            foreach($productsInInvoice as $pr){
                array_push($productsInInvoiceArray,$pr['product_id']);
            }

            foreach ($request->credit_note['line_items'] as $item){
                $productItem = ProductService::where('sku',$item['product_id'])->first();
                if(in_array($productItem->id,$productsInInvoiceArray)){
                    $valueTobeSubtracted = $item['quantity']*$item['unit_price']; // number of items * price of each item
                    $amount=$amount + $valueTobeSubtracted;
                }
                $description = $item['description'] ?? " ";
                $productService = ProductService::where('sku',$item['product_id'])->first();
                $productService->quantity = $productService->quantity + $item['quantity'];
                $productService->save();
            }

            //continue working on invoice
//            if($amount > $invoiceDue->getDue())
//            {
//                return $this->error('maximum '. \Auth::user()->priceFormat($invoiceDue->getDue()) .  ' credit limit of this invoice. ');
//            }
            $invoice = $invoiceDue;

            $credit              = new CreditNote();
         //   $credit->invoice     = $request->credit_note['invoice_id'];
            $credit->invoice     = $invoiceDue->id;
            $credit->customer    = $request->credit_note['contact_id'];
            $credit->date        = $request->credit_note['issue_date'];
            $credit->amount      = $amount;
            $credit->description = $description ?? " ";
            $credit->save();

            Utility::userBalance('customer', $request->credit_note['contact_id'], $amount, 'credit');
            return response()->json(['credit'=> $credit]);
        }
        else
        {
            return response()->json(['message'=>"Permission denied."]);
        }
    }


    function invoiceNumber()
    {
        $latest = Invoice::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function getCustomerById($id){
        $customer = Customer::where('customer_id',$id)->where('created_by',Auth::user()->id)->first();
        if(!$customer){
            return response()->json(['error'=>"no customer found"]);
        }
        return response()->json(['customer'=>$customer]);
    }

    public function refundCustomerPayment(Request $request,$id){
        if(\Auth::user()->can('create credit note'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'invoice'=>[
                        'allocations_attributes.amount' => 'required',
                        'allocations_attributes.date' => 'required'
                    ]
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['messages'=>$messages]);

            }
            $invoice_id = $id;
            $invoiceDue = Invoice::where('invoice_id', $invoice_id)->first();

            if($request->amount > $invoiceDue->getDue())
            {
                return response()->json(['error'=>'Maximum ' . \Auth::user()->priceFormat($invoiceDue->getDue()) . ' credit limit of this invoice.']);
            }
            $invoice             = Invoice::where('invoice_id', $invoice_id)->first();
            $credit              = new CreditNote();
            $credit->invoice     = $invoiceDue->id;
            $credit->customer    = $invoiceDue->customer_id;
            $credit->date        =$request->invoice['allocations_attributes'][0]['date'];
            $credit->amount      = $request->invoice['allocations_attributes'][0]['amount'];
            $credit->description = "";
            $credit->save();
            Utility::userBalance('customer', $invoice->customer_id, $request->invoice['allocations_attributes'][0]['amount'], 'debit');
            return response()->json(['credit'=>$credit]);
        }
        else
        {
            return response()->json(['error'=>'Permission denied.']);
        }
    }


}
