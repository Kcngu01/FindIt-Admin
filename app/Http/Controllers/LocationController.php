<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Item;

class LocationController extends Controller
{
    //
    public function index(){
        $locations = Location::all();
        return view('location', compact('locations'));
    }

    public function store(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:locations,name'
            ]);

            $name = $request->name;
            $location = new Location();
            $location->name = $name;
            $location->save();
            return redirect()->route('location.index')->with('success','New Location Added Successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', 'Location already exists');
            // return redirect()->back()->with('error', 'Colour already exists')->withErrors($e->validator);
        }
    }

    public function update(Request $request, $id){
        $location = Location::find($id);
        
        if(!$location){
            return redirect()->route('location.index')->with('error','Locationnot found');
        }

        // Check if the category is referenced in any items
        $referencedItems = Item::where('location_id', $id)->count();
        if ($referencedItems > 0) {
            return redirect()->route('location.index')->with('error', 'Cannot update location: it is referenced by ' . $referencedItems . ' item(s)');
        }
        
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:locations,name,' . $id
            ]);
            
            $location->name = $request->name;
            $location->save();
            return redirect()->route('location.index')->with('success', 'Location updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', 'Locationname already exists');
        }
    }

    public function destroy($id){
        $location = Location::find($id);
        if($location){
            // Check if the location is referenced in any items
            $referencedItems = Item::where('location_id', $id)->count();
            if ($referencedItems > 0) {
                return redirect()->route('location.index')->with('error', 'Cannot delete location: it is referenced by ' . $referencedItems . ' item(s)');
            }
            
            $location->delete();
            return redirect()->route('location.index')->with('success','Location deleted successfully');
        }
        return redirect()->route('location.index')->with('error','Location not found');
    }
}
