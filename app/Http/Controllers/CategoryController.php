<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
class CategoryController extends Controller
{
    //
    public function index(){
        $categories = Category::all();
        return view('category',compact('categories'));
    }

    public function store(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name'
            ]);

            $name = $request->name;
            $category = new Category();
            $category->name = $name;
            $category->save();
            return redirect()->route('category.index')->with('success','New Category Added Successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', 'Category already exists');
            // return redirect()->back()->with('error', 'Colour already exists')->withErrors($e->validator);
        }
    }

    public function update(Request $request,$id){
        $category = Category::find($id);

        if(!$category){
            return redirect()->route('category.index')->with('error','Category not found');
        }

        //check if the category is referenced in any items
        $referencedItems = Item::where('category_id', $id)->count();
        if ($referencedItems > 0) {
            return redirect()->route('category.index')->with('error', 'Cannot update category: it is referenced by ' . $referencedItems . ' item(s)');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id
            ]);
            $category->name= $request->name;
            $category->save();
            return redirect()->route('category.index')->with('success','Category updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', 'Category already exists');
        }
    }

    public function destroy($id){
        $category = Category::find($id);
        if($category){
            // Check if the category is referenced in any items
            $referencedItems = Item::where('category_id', $id)->count();
            if ($referencedItems > 0) {
                return redirect()->route('category.index')->with('error', 'Cannot delete category: it is referenced by ' . $referencedItems . ' item(s)');
            }
            $category->delete();
            return redirect()->route('category.index')->with('success','Category deleted successfully');
        }
        return redirect()->route('category.index')->with('error','Category not found');
    }
}
