<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colour;
use App\Models\Item;

class ColourController extends Controller
{
    //
    public function index(){
        $colours = Colour::all();
        return view('colour',compact('colours'));
    }

    public function store(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:colours,name'
            ]);

            $name = $request->name;
            $colour = new Colour();
            $colour->name = $name;
            $colour->save();
            return redirect()->route('colour.index')->with('success','New Colour Added Successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', 'Colour already exists');
            // return redirect()->back()->with('error', 'Colour already exists')->withErrors($e->validator);
        }

        // Check if colour already exists (case insensitive)
        // $existingColour = Colour::whereRaw('LOWER(name) = ?', [strtolower($request->name)])->first();
        // dd($existingColour);
        // if ($existingColour) {
        //     // Add a log entry to verify this condition is triggered
        //     log('Duplicate colour detected: ' . $request->name);
            
        //     return redirect()->route('colour.index')->with('error', 'Colour already exists.');
        // }
    }

    public function update(Request $request,$id){
        $colour = Colour::find($id);

        if(!$colour){
            return redirect()->route('category.index')->with('error','Colour not found');
        }

        // Check if the category is referenced in any items
        $referencedItems = Item::where('color_id', $id)->count();
        if ($referencedItems > 0) {
            return redirect()->route('colour.index')->with('error', 'Cannot update colour: it is referenced by ' . $referencedItems . ' item(s)');
        }
        
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:colours,name,' . $id
            ]);
            
            $colour->name = $request->name;
            $colour->save();
            return redirect()->route('colour.index')->with('success', 'Colour updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', 'Colour name already exists');
        }
    }

    public function destroy($id){
        $colour = Colour::find($id);
        if($colour){
            // Check if the colour is referenced in any items
            $referencedItems = Item::where('color_id', $id)->count();
            if ($referencedItems > 0) {
                return redirect()->route('colour.index')->with('error', 'Cannot delete colour: it is referenced by ' . $referencedItems . ' item(s)');
            }
            
            $colour->delete();
            return redirect()->route('colour.index')->with('success','Colour deleted successfully');
        }
        return redirect()->route('colour.index')->with('error','Colour not found');
    }
}
