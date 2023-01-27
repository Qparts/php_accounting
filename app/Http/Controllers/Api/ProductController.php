<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Utility;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ApiResponse;


    public function createCategory(Request $request)
    {
        if(\Auth::user()->can('create constant category'))
        {

            $validator = \Validator::make(
                $request->all(), [
                    'category.name' => 'required|max:20'
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $category             = new ProductServiceCategory();
            $category->name       = $request->category['name'];
            $category->color      = $this->rand_color();
            $category->type       = 0;
            $category->created_by = \Auth::user()->creatorId();
            $category->save();
            return $this->success($category,"success");
        }
        else
        {
            return $this->error("Permission denied.",401);
        }
    }

    public function createProduct(Request $request)
    {


        if(\Auth::user()->can('create product & service'))
        {

            $rules = [

                'product.name_ar' => 'required',
                'product.name_en' => 'required',
                'product.sku'=>'required',
                'product.description'=>'required',
                'product.product_unit_type_id'=>'required',
                'product.track_quantity'=>'required',
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

         //   return $this->success($productService,"success");
            return response()->json([
                'success' => true,
                'code' => 201,
                'message' => "success",
                'data'   => ['product' => $productService],
                'locale' => app()->getLocale(),
            ], 201, [], JSON_INVALID_UTF8_SUBSTITUTE);
        }
        else
        {
            return $this->error("something went wrong",409);
        }
    }

    private function rand_color() {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    public function getProduct($sku){
        $product = ProductService::where('sku',$sku)->first();
        if(!$product){
            return $this->error("product not found",404);
        }
        return $this->success($product,"success");
    }
}
