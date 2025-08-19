<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


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

        // Generate base slug
        $slug = Str::slug($validated['title']);

        // Ensure uniqueness
        $originalSlug = $slug;
        $count = 1;

        while (BusinessType::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $businesstypes->slug = $slug;

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

        // Generate a new slug
        $slug = Str::slug($validatedData['title']);

        // Ensure unique slug (ignore current record's slug when updating)
        $originalSlug = $slug;
        $counter = 1;

        while (
            BusinessType::where('slug', $slug)
            ->where('id', '!=', $businesstype->id)
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $businesstype->slug = $slug;

        $save = $businesstype->save();

        if ($save) {
            return redirect()->route('businesstypes.index')->with('success', 'Business Type updated successfully!');
        } else {
            return redirect()->route('businesstypes.index')->with('error', 'Error occurred while updating');
        }
    }

    public function delete($id)
    {
        $user = BusinessType::findOrFail($id);
        $user->delete();

        return redirect()->route('businesstypes.index')->with('success', 'Business Type deleted successfully!');
    }
}
