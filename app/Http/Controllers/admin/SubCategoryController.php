<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $subCategories = SubCategory::select('sub_categories.*', 'categories.name as categoryName')
                                    ->orderBy('sub_categories.id', 'ASC')
                                    ->leftJoin('categories', 'categories.id', 'sub_categories.category_id');

            if (!empty($request->get('keyword'))) {
                $subCategories = $subCategories->where('sub_categories.name', 'like','%' . $request->get('keyword') . '%');
                $subCategories = $subCategories->orWhere('categories.name', 'like','%' . $request->get('keyword') . '%');
            }

        $subCategories = $subCategories->paginate(10);
        return view('admin.sub_category.list', compact('subCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {
            
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', 'Sub Category created successfully');
            
            return response([
                'status' => true,
                'message' => 'Sub Category created successfully'
            ]);

        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            session()->flash('error', 'Record not found');
            return redirect()->route('sub-categories.index');
        }

        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;
        return view('admin.sub_category.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, Request $request)
    {
        $subCategory = SubCategory::find($id);

        if (empty($subCategory)) {
            session()->flash('error', 'Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $subCategory->id . ',id',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {
            
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', 'Sub Category updated successfully');
            
            return response([
                'status' => true,
                'message' => 'Sub Category updated successfully'
            ]);

        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($subCategoryId, Request $request)
    {
        $subCategory = SubCategory::find($subCategoryId);
        if (empty($subCategory)) {
            session()->flash('error', 'Sub Category not found');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category not found'
            ]);
        }

        $subCategory->delete();

        session()->flash('success', 'Sub Category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Sub Category deleted successfully'
        ]);
    }
}
