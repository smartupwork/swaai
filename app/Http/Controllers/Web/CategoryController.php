<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return view('admin.category.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cat_color' => 'nullable|string|size:7',
        ]);


        $category = new Category();
        $category->name = $validated['name'];
        $category->description = $validated['description'] ?? null;
        $category->cat_color = $validated['cat_color'];
        $category->save();


        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.category.edit')->with('category', $category);
    }

    public function update(Request $request, $id)
    {

        $category = Category::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'string|required|max:30',
            'description' => 'string|nullable',
            'cat_color' => 'nullable|string|size:7',
        ]);

        $category->name = $validatedData['name'];
        $category->description = $validatedData['description'];
        $category->cat_color = $validatedData['cat_color'];
       

        $save = $category->update();
        if ($save) {
            return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
        } else {
            request()->session('error', 'Error occured while updating');
        }
        return redirect()->route('categories.index')->with('error', 'Sorry, something wrong.');
    }

    public function delete($id)
    {
        $user = Category::findOrFail($id);
        $user->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
