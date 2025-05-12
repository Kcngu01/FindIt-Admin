<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Item;
use App\Models\Category;
use App\Models\Claim;
use App\Models\ItemMatch;
use App\Models\Location;
use App\Models\Colour;
use App\Models\Student;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function register(Request $request){
        $credentials = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:students|regex:/^[a-zA-Z0-9._%+-]+@siswa\.unimas\.my$/',
            'password' => 'required|string|min:8',
            'matric_no' => 'required|integer|unique:students,matric_no',
        ],[
            'email.regex' => 'The email must end with @siswa.unimas.my',
        ]);

        // Create the student with hashed password
        $student = Student::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
            'matric_no' => $credentials['matric_no'],
        ]);

        // Generate API token for the student
        $token = $student->createToken('auth_token')->plainTextToken;

        // Trigger the verification email
        event(new Registered($student));

        return response()->json([
            'success' => true,
            'message' => 'Student registered successfully. Please check your email for verification link.',
            'token' => $token,
            'user' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'matric_no' => $student->matric_no,
                'email_verified' => false,
            ],
        ], 201);
    }

    public function login(Request $request){
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if (Auth::guard('student')->attempt($credentials)) {
            $user = Auth::guard('student')->user();
            $token = $user->createToken('mobile-app-token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'matricNo' => $user->matric_no,
                    'email_verified' => !is_null($user->email_verified_at),
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request){
        try {
            // Delete all FCM tokens associated with the current user
            \App\Models\FcmToken::where('student_id', $request->user()->id)->delete();
            
            // Delete the current access token
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error during logout: ' . $e->getMessage());
            
            // Still try to delete the current token even if FCM token deletion failed
            try {
                $request->user()->currentAccessToken()->delete();
            } catch (\Exception $ex) {
                // Just log this error but continue
                \Illuminate\Support\Facades\Log::error('Error deleting access token: ' . $ex->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        }
    }

    public function getItems(Request $request){
        $type = $request->input('type');
        $categoryId = $request->input('category_id');
        $locationId = $request->input('location_id');
        $colourId = $request->input('color_id');
        $search = $request->input('search');


        if($type == 'lost'){
            $query = Item::where('type', 'lost');
            
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            
            if ($locationId) {
                $query->where('location_id', $locationId);
            }
            
            if ($colourId) {
                $query->where('color_id', $colourId);
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $items = $query->get();
        }else if($type == 'found'){
            $query = Item::where('type', 'found');
            
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            
            if ($locationId) {
                $query->where('location_id', $locationId);
            }
            
            if ($colourId) {
                $query->where('color_id', $colourId);
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $items = $query->get();
        }else if($type == 'recovered'){
            $query = Item::where('type', 'recovered');
            
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            
            if ($locationId) {
                $query->where('location_id', $locationId);
            }
            
            if ($colourId) {
                $query->where('color_id', $colourId);
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $items = $query->get();
        }else{
            $items = Item::all();
        }

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    function getItemsByStudentId(Request $request){
        $studentId = $request->input('student_id');
        $type = $request->input('type');
        $items = Item::where('student_id', $studentId)->where('type', $type)->get();
        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    public function getCategories(){
        $categories = Category::all();
        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function getColours(){
        $colours= Colour::all();
        return response()->json([
            'success' => true,
            'colours' => $colours
        ]);
    }

    public function getLocations(){
        $locations = Location::all();
        return response()->json([
            'success' => true,
            'locations' => $locations
        ]);
    }


    public function createItem(Request $request){
        $data = $request->all();
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            if($data['type'] == 'lost'){
                $path = $file->storeAs('lost_items', $filename,'public');
            }else if($data['type'] == 'found'){
                $path = $file->storeAs('found_items', $filename,'public');
            }
            $data['image'] = $filename;
        }
        
        $item = Item::create($data);
        
        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    public function getItemById($id){
        $item = Item::find($id);
        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    public function getCategoryById($id){
        $category = Category::find($id);
        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    public function getLocationById($id){
        $location = Location::find($id);
        return response()->json([
            'success' => true,
            'location' => $location
        ]);
    }   

    public function getColourById($id){
        $colour = Colour::find($id);
        return response()->json([
            'success' => true,
            'colour' => $colour
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function updateItem(Request $request, string $id){
        try {
            // Get item
            $item = Item::find($id);
            
            if (!$item) {
                Log::error('Item not found', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }
            
            // For PATCH requests with multipart/form-data, extract fields directly
            $input = [];
            
            // Debug the request data
            Log::info('Request method and content type', [
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'has_files' => $request->hasFile('image') ? 'Yes' : 'No'
            ]);
            
            // Extract fields manually from the request
            if ($request->has('name')) {
                $input['name'] = $request->input('name');
                $item->name = $input['name'];
                Log::info('Name found in request', ['name' => $input['name']]);
            }
            
            if ($request->has('description')) {
                $input['description'] = $request->input('description');
                $item->description = $input['description'];
                Log::info('Description found in request', ['description' => $input['description']]);
            }
            
            if ($request->has('category_id')) {
                $input['category_id'] = $request->input('category_id');
                $item->category_id = $input['category_id'];
                Log::info('Category ID found in request', ['category_id' => $input['category_id']]);
            }
            
            if ($request->has('color_id')) {
                $input['color_id'] = $request->input('color_id');
                $item->color_id = $input['color_id'];
                Log::info('Color ID found in request', ['color_id' => $input['color_id']]);
            }
            
            if ($request->has('location_id')) {
                $input['location_id'] = $request->input('location_id');
                $item->location_id = $input['location_id'];
                Log::info('Location ID found in request', ['location_id' => $input['location_id']]);
            }
            
            if ($request->has('type')) {
                $input['type'] = $request->input('type');
                $item->type = $input['type'];
                Log::info('Type found in request', ['type' => $input['type']]);
            }
            
            // Validate the data
            $validator = Validator::make($input, [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category_id' => 'sometimes|exists:categories,id',
                'color_id' => 'sometimes|exists:colours,id',
                'location_id' => 'sometimes|exists:locations,id',
                'type' => 'sometimes|string|in:lost,found',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            if ($validator->fails()) {
                Log::warning('Validation failed for item update', [
                    'id' => $id,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle image upload if provided
            if ($request->hasFile('image')) {
                try {
                    $oldImage = $item->image;
                    $file = $request->file('image');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    
                    if($item->type == 'lost'){
                        $path = $file->storeAs('lost_items', $filename, 'public');
                    } else if($item->type == 'found'){
                        $path = $file->storeAs('found_items', $filename, 'public');
                    }
                    
                    $item->image = $filename;
                    Log::info('Image uploaded successfully', ['filename' => $filename]);
                } catch (\Exception $e) {
                    Log::error('Image upload failed', [
                        'id' => $id,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Image upload failed: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Save the item
            try {
                $item->save();
                if ($request->hasFile('image') && !empty($oldImage)){
                    $oldPath = ($item->type == 'lost')? 'lost_items/'.$oldImage : 'found_items/'.$oldImage;
                    if (Storage::disk('public')->exists($oldPath)) {
                        try {
                            Storage::disk('public')->delete($oldPath);
                            Log::info('Old image deleted successfully', ['path' => $oldPath]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to delete old image', [
                                'path' => $oldPath,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    Log::info('Item saved successfully', ['id' => $id]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to save item', [
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save item: ' . $e->getMessage()
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating item', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function deleteItem(string $id) {
        try {
            $item = Item::find($id);
            
            if (!$item) {
                Log::error('Item not found for deletion', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }
            
            $item->delete();
            $oldImage = $item->image;
            $oldPath = ($item->type == 'lost')? 'lost_items/'.$oldImage : 'found_items/'.$oldImage;
            if (Storage::disk('public')->exists($oldPath)) {
                try {
                    Storage::disk('public')->delete($oldPath);
                    Log::info('Old image deleted successfully', ['path' => $oldPath]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old image', [
                        'path' => $oldPath,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Item deleted successfully', ['id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting item', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function claimItem(Request $request){
        $data = $request->all();
        $claim = Claim::create($data);
        return response()->json([
            'success' => true,
            'claim' => $claim
        ]);
    }

    public function checkClaim(Request $request){
        // $data = $request->all();
        $request->validate([
            'found_item_id' => 'required|exists:items,id',
            'student_id' => 'required|exists:students,id',
        ]);
    
        $claim = Claim::where('found_item_id', $request->found_item_id)
            ->where('student_id', $request->student_id)
            ->exists();
    
        return response()->json([
            'success' => true,
            'claimed' => $claim
        ]);
    }


    // retrieve all claims of a student
    public function getAllClaims(int $studentId){
        $claims = Claim::where('student_id', $studentId)->with('foundItem')->get();
        return response()->json([
            'success' => true,
            'claims' => $claims,
        ]);
    }

    public function getClaimDetails(int $id){
        $claim = Claim::with(['foundItem','student','admin','foundItem.category','foundItem.color','foundItem.location'])->find($id);

        return response()->json([
            'success' => true,
            'claim' => $claim
        ]);
    }
}
