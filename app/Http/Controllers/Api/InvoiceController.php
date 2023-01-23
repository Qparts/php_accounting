<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                    'invoice.description' => 'required',
                    'invoice.issue_date' => 'required',
                    'invoice.due_date' => 'required',
                    'invoice.status' => 'required',
                    'invoice.inventory_id' => 'required',
                    'invoice.line_items' => 'required'
                ]

            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return $this->error($messages,409);
            }

            $invoice                 = new Invoice();
            $invoice->invoice_id     = $this->invoiceNumber();
           // $invoice->customer_id    = $request->customer_id;
            $invoice->customer_id    = $request->invoice['contact_id'];
            $invoice->status         = 0;
            $invoice->issue_date     = $request->invoice['issue_date'];
            $invoice->send_date     = $request->invoice['issue_date'];
            $invoice->due_date       = $request->invoice['due_date'];
            $invoice->category_id    = $request->invoice['inventory_id'];
            //$invoice->ref_number     = $request->ref_number;
            $invoice->ref_number     = $request->invoice['reference'];
            $invoice->created_by     = Auth::user()->creatorId();
            $invoice->save();
            CustomField::saveData($invoice, $request->custom_fields);
            $products = $request->invoice['line_items'];
            for($i = 0; $i < count($products); $i++)
            {

                $invoiceProduct              = new InvoiceProduct();
                $invoiceProduct->invoice_id  = $invoice->id;
                $invoiceProduct->product_id  = $products[$i]['product_id'];
                $invoiceProduct->quantity    = $products[$i]['quantity'];
                $invoiceProduct->tax         = $products[$i]['tax_percent'];
                $invoiceProduct->discount    = $products[$i]['discount'];
                $invoiceProduct->price       = $products[$i]['unit_price'];
                $invoiceProduct->description = $products[$i]['description'];
                $invoiceProduct->save();
                //inventory management (Quantity)
                Utility::total_quantity('minus',$invoiceProduct->quantity,$invoiceProduct->product_id);

            }

            $type='invoice';
            $type_id = $invoice->id;
            StockReport::where('type','=','invoice')->where('type_id' ,'=', $invoice->id)->delete();
            $description=$invoiceProduct->quantity.'  '.__(' quantity sold in invoice').' '. Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            Utility::addProductStock( $invoiceProduct->product_id,$invoiceProduct->quantity,$type,$description,$type_id);

            return $this->success(["msg"=>"invoice created"],"success");
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
                'invoice_payment.invoice_id'=>'required',
                'invoice_payment.account_id'=>'required',
                'invoice_payment.date'=>'required',
                'invoice_payment.amount'=>'required',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return $this->error($messages,409);
            }

            $revenue                 = new Revenue();
            $revenue->date           = $request->invoice_payment['date'];
            $revenue->amount         = $request->invoice_payment['amount'];
            $revenue->account_id     = $request->invoice_payment['account_id'];
            $revenue->customer_id    = $request->invoice_payment['customer_id'];
            $revenue->category_id    = $request->invoice_payment['category_id']?? 0;
            $revenue->payment_method = 0;
            $revenue->reference      = $request->invoice_payment['reference'];

            $revenue->created_by     = \Auth::user()->creatorId();
            $revenue->save();

            $category            = ProductServiceCategory::where('id', $request->invoice_payment['category_id'])->first();


            $revenue->payment_id = $revenue->id;
            $revenue->type       = 'Revenue';
            $revenue->category   = $category->name;
            $revenue->user_id    = $revenue->customer_id;
            $revenue->user_type  = 'Customer';
            $revenue->account    = $request->invoice_payment['account_id'];
            Transaction::addTransaction($revenue);

            $customer         = Customer::where('id', $request->invoice_payment['customer_id'])->first();
            $payment          = new InvoicePayment();
            $payment->name    = !empty($customer) ? $customer['name'] : '';
            $payment->date    = \Auth::user()->dateFormat($request->invoice_payment['date']);
            $payment->amount  = \Auth::user()->priceFormat($request->invoice_payment['amount']);
            $payment->invoice = '';

            if(!empty($customer))
            {
                Utility::userBalance('customer', $customer->id, $revenue->amount, 'credit');
            }

            Utility::bankAccountBalance($request->invoice_payment['account_id'], $revenue->amount, 'credit');

            return $this->success($revenue,"success");
        }
        else
        {
            return $this->error("you don't have permission",401);
        }
    }




}
