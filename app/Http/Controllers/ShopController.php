<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null)
    {
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        if (!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
        }

        $categories = Category::orderBy('name', 'ASC')
            ->with('sub_category')
            ->where('status', 1)
            ->get();

        $brands = Brand::orderBy('name', 'ASC')
            ->where('status', 1)
            ->get();

        $products = Product::where('status', 1);

        // Get the ID of the selected category (if any)
        $category = null;
        if (!empty($categorySlug)) {
            $category = Category::where('slug', $categorySlug)->first();
            $categorySelected = $category->id;
        }

        // Get the ID of the selected subcategory (if any)
        $subCategory = null;
        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug', $subCategorySlug)->first();
            $subCategorySelected = $subCategory->id;
        }         
        
        // Apply filters based on selected brands
        if (!empty($brandsArray)) {
            $products->whereIn('brand_id', $brandsArray);
        }            

        // Filter products based on category and subcategory (if defined)
        if ($category) {
            $products->where('category_id', $category->id);
        }

        if ($subCategory) {
            $products->where('sub_category_id', $subCategory->id);
        }
        
        if ($request->get('price_max') != '' && $request->get('price_min') != '') {
            if ($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), intval($request->get('price_max'))]);
            }
        }

        if (!empty($request->get('search'))) {
            $products = $products->where('title', 'like', '%' . $request->get('search') . '%');
        }

        if ($request->get('sort') != '') {
            if ($request->get('sort') == 'latest') {
                $products = $products->orderBy('id', 'DESC');                
            } elseif ($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'ASC');                
            } else {
                $products = $products->orderBy('price', 'DESC');
            }
        } else {
            $products = $products->orderBy('id', 'DESC');
        }
        
        $products = $products->paginate(6);

        // Now, you can filter brands based on the selected category
        $brands = Brand::whereHas('products', function ($query) use ($categorySelected, $subCategorySelected) {
            $query->where('status', 1);

            if (!empty($categorySelected)) {
                $query->where('category_id', $categorySelected);
            }

            if (!empty($subCategorySelected)) {
                $query->where('sub_category_id', $subCategorySelected);
            }
        })->orderBy('name', 'ASC')
          ->where('status', 1)
          ->get();        

        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;
        $data['categorySelected'] = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;
        $data['brandsArray'] = $brandsArray;
        $data['priceMax'] = (intval($request->get('price_max')) == 0) ? 1000 : $request->get('price_max');
        $data['priceMin'] = intval($request->get('price_min'));
        $data['sort'] = $request->get('sort');

        return view('front.shop', $data);
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)->with('product_images')->first();
        if ($product == null) {
            abort(404);
        }

        // Fetch Product Images
        $relatedProducts = [];

        if ($product->related_products != '') {
            $productArray = explode(',', $product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->where('status', 1)->get();
        }

        $data['product'] = $product;
        $data['relatedProducts'] = $relatedProducts;

        return view('front.product', $data);
    }
}
