<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\ProductService;
use App\Models\Utility;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ApiResponse;


    public function createProduct(Request $request)
    {


        if(\Auth::user()->can('create product & service'))
        {

            $rules = [
//                'sku' => ['required', Rule::unique('product_services')->where(function ($query) {
//                    return $query->where('created_by', \Auth::user()->id);
//                })
         //   ],
//                'product.sale_price' => 'required|numeric',
//                'purchase_price' => 'required|numeric',
//                'category_id' => 'required',
//                'unit_id' => 'required',
//                'type' => 'required',
//                'pro_image' => 'mimes:jpeg,png,jpg,gif,pdf,doc,zip|max:20480',
                'product.name_ar' => 'required',
                'product.name_en' => 'required',
                'product.sku'=>'required',
                'product.barcode'=>'required',
                'product.description'=>'required',
                'product.product_unit_type_id'=>'required',
                'product.track_quantity'=>'required',
                'product.purchase_item'=>'required',
                'product.buying_price'=>'required',
                'product.expense_account_id'=>'required',
                'product.sale_item'=>'required',
                'product.selling_price'=>'required',
                'product.sales_account_id'=>'required',
                'product.tax_id'=>'required'
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return $this->error($messages,"409");
            }

            $productService                 = new ProductService();
            $productService->name           = $request->product['name_ar'] .' / '.$request->product['name_en'];
            $productService->description    = $request->product['description'];
            $productService->sku            = $request->product['sku'];
            $productService->sale_price     = $request->product['selling_price'];
            $productService->purchase_price = $request->product['buying_price'];
            $productService->tax_id         = $request->product['tax_id'] ?? NULL;
            $productService->unit_id        = $request->product['product_unit_type_id'];
            if(!empty($request->product['track_quantity']))
            {
                $productService->quantity        = $request->product['track_quantity'];
            }
            else{
                $productService->quantity   = 0;
            }

            $productService->type           = "product";
            $productService->category_id    = $request->product['category_id'] ?? 1;

            if(!empty($request->product['pro_image']))
            {

                if($productService->pro_image)
                {
                    $path = storage_path('uploads/pro_image' . $productService->pro_image);
                    if(file_exists($path))
                    {
                        \File::delete($path);
                    }
                }
                $fileName = $request->product['pro_image']->getClientOriginalName();
                $productService->pro_image = $fileName;
                $dir        = 'uploads/pro_image';
                $path = Utility::upload_file($request,'pro_image',$fileName,$dir,[]);
            }

            $productService->created_by     = \Auth::user()->creatorId();
            $productService->save();
          //  CustomField::saveData($productService, $request->customField);

            return $this->success($productService,"success");
        }
        else
        {
            return $this->error("something went wrong",409);
        }
    }
}
