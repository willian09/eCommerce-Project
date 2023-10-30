<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pages = Page::orderBy('id', 'ASC');

        if (!empty($request->get('keyword'))) {
            $pages = $pages->where('name', 'like', '%' . $request->get('keyword') . '%');
            $pages = $pages->orWhere('slug', 'like', '%' . $request->get('keyword') . '%');
        }
        $pages = $pages->paginate(10);
        return view('admin.pages.list', [
            'pages' => $pages
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->passes()) {

            $page = new Page();
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();

            $message = 'Page added successfully';
            session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message
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
    public function edit(string $id)
    {
        $page = Page::find($id);

        if ($page == null) {
            $message = 'Page not found';
            session()->flash('error', $message);
            return redirect()->route('pages.index');
        }

        return view('admin.pages.edit' ,[
            'page' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $page = Page::find($id);

        if ($page == null) {
            $message = 'Page not found';
            session()->flash('error', $message);
            return view('admin.pages.edit' ,[
                'status' => true,
                'message' => $message
            ]);    
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->passes()) {

            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->update();

            $message = 'Page updated successfully';
            session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message
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
    public function destroy(string $id)
    {
        $page = Page::find($id);
        if (empty($page)) {
            $message = 'Page not found';
            session()->flash('error', $message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        }

        $page->delete();

        $message = 'Page deleted successfully';
        session()->flash('success', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
