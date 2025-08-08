<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\Request;

class BusinessTypeController extends Controller
{
    public function index()
    {
        $buisnesstypes = BusinessType::all();
        return view('admin.businesstypes.index', ['buisnesstypes' => $buisnesstypes]);
    }

    public function create()
    {
        return view('admin.businesstypes.create');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);


        $businesstypes = new BusinessType();
        $businesstypes->title = $validated['title'];
        $businesstypes->save();


        return redirect()->route('businesstypes.index')->with('success', 'Business Type created successfully!');
    }

    public function edit($id)
    {
        $businesstype = BusinessType::findOrFail($id);

        return view('admin.businesstypes.edit')->with('businesstype', $businesstype);
    }

    public function update(Request $request, $id)
    {

        $businesstype = BusinessType::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'string|required|max:30',
        ]);

        $businesstype->title = $validatedData['title'];
       
        $save = $businesstype->update();
        if ($save) {
            return redirect()->route('businesstypes.index')->with('success', 'Business Type updated successfully!');
        } else {
            request()->session('error', 'Error occured while updating');
        }
        return redirect()->route('businesstypes.index')->with('error', 'Sorry, something wrong.');
    }

    public function delete($id)
    {
        $user = BusinessType::findOrFail($id);
        $user->delete();

        return redirect()->route('businesstypes.index')->with('success', 'Business Type deleted successfully!');
    }
}
