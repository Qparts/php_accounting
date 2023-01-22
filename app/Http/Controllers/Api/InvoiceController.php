<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\StockReport;
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

            //prerequisites
            $customFields   = CustomField::where('created_by', '=', Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
//            $invoice_number = Auth::user()->invoiceNumberFormat($this->invoiceNumber());
//            $customers      = Customer::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
//            $customers->prepend('Select Customer', '');
//            $category = ProductServiceCategory::where('created_by', Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
//            $category->prepend('Select Category', '');
//            $product_services = ProductService::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
//            $product_services->prepend('--', '');
            //creation of invoice
            $validator = \Validator::make(
                $request->all(), [
                    'customer_id' => 'required',
                    'issue_date' => 'required',
                    'due_date' => 'required',
                    'category_id' => 'required',
                    'items' => 'required',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $status = Invoice::$statues;
            $invoice                 = new Invoice();
            $invoice->invoice_id     = $this->invoiceNumber();
            $invoice->customer_id    = $request->customer_id;
            $invoice->status         = 0;
            $invoice->issue_date     = $request->issue_date;
            $invoice->due_date       = $request->due_date;
            $invoice->category_id    = $request->category_id;
            $invoice->ref_number     = $request->ref_number;

            $invoice->created_by     = Auth::user()->creatorId();
            $invoice->save();
            CustomField::saveData($invoice, $request->customField);
            $products = $request->items;

            for($i = 0; $i < count($products); $i++)
            {

                $invoiceProduct              = new InvoiceProduct();
                $invoiceProduct->invoice_id  = $invoice->id;
                $invoiceProduct->product_id  = $products[$i]['item'];
                $invoiceProduct->quantity    = $products[$i]['quantity'];
                $invoiceProduct->tax         = $products[$i]['tax'];
//                $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                $invoiceProduct->discount    = $products[$i]['discount'];
                $invoiceProduct->price       = $products[$i]['price'];
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

    function invoiceNumber()
    {
        $latest = Invoice::where('created_by', '=', Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function listCategory(){
        $category = ProductServiceCategory::where('created_by', Auth::user()->creatorId())->where('type', 1)->get()->pluck('name', 'id');
        if($category){
            return $this->success($category,"success");
        }
        return $this->error("no categories",404);

    }

    public function listInvoiceNumber(){
        $invoice_number = Auth::user()->invoiceNumberFormat($this->invoiceNumber());
        if($invoice_number){
            return $this->success(["invoice_number"=>$invoice_number],"success");
        }
        return $this->error("no invoices",404);
    }

    public function productService(){
        $product_services = ProductService::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
        if($product_services){
            return $this->success(["products"=>$product_services],"success");
        }
        return $this->error("no products",404);
    }

}
