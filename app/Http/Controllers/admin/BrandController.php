<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = Brand::orderBy('id', 'ASC');

            if (!empty($request->get('keyword'))) {
                $brands = $brands->where('brands.name', 'like','%' . $request->get('keyword') . '%');
            }

        $brands = $brands->paginate(10);
        return view('admin.brands.list', compact('brands'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {        
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            session()->flash('success', 'Brand added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Brand added successfully'
            ]);    
        } else {
            return response()->json([
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
    public function edit(Request $request, $brandId)
    {
        $brand = Brand::find($brandId);
        if (empty($brand)) {
            return redirect()->route('brands.index');
        }
        
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $brandId)
    {
        $brand = Brand::find($brandId);

        if (empty($brand)) {
            session()->flash('error', 'Record not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Record not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $brand->id . ',id',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            session()->flash('success', 'Brand updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Brand updated successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $brandId)
    {
        $brand = Brand::find($brandId);
        if (empty($brand)) {
            session()->flash('error', 'Brand not found');
            return response()->json([
                'status' => true,
                'message' => 'Brand not found'
            ]);
        }

        $brand->delete();

        session()->flash('success', 'Brand deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully'
        ]);
    }
}