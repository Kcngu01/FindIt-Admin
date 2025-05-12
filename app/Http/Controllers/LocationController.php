<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

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
        $newName = $request->name;
        $location->name = $newName;
        $location->save();
        return redirect()->route('location.index')->with('success','Location updated successfully');
    }

    public function destroy($id){
        $location = Location::find($id);
        if($location){
            $location->delete();
            return redirect()->route('location.index')->with('success','Location deleted successfully');
        }
        return redirect()->route('location.index')->with('error','Location not found');
    }
}
