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
use App\Http\Controllers\API\ImageSimilarityController;
use App\Services\MatchNotificationService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\FcmToken;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Admin;
use App\Models\Faculty;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class ApiController extends Controller
{
    protected $notificationService;

    public function __construct(MatchNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function register(Request $request){
        // Log the entire request for debugging
        \Illuminate\Support\Facades\Log::info('Registration request data:', [
            'all_data' => $request->all(),
            'has_matric_no' => $request->has('matric_no'),
            'matric_no_value' => $request->input('matric_no'),
            'matric_no_type' => gettype($request->input('matric_no')),
        ]);

        // Make sure matric_no is present and is a valid integer
        if (!$request->has('matric_no') || !is_numeric($request->input('matric_no'))) {
            return response()->json([
                'success' => false,
                'message' => 'Matric number is required and must be a valid number.',
                'error_details' => [
                    'provided_value' => $request->input('matric_no'),
                    'type' => gettype($request->input('matric_no'))
                ]
            ], 422);
        }

        // Convert to int explicitly here
        $matricNo = intval($request->input('matric_no'));
        
        // Validate after ensuring matric_no is an integer
        $credentials = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:students|regex:/^[0-9]+@siswa\.unimas\.my$/',
            'password' => 'required|string|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/',
            'matric_no' => 'required|integer|unique:students,matric_no',
        ],[
            'email.regex' => 'The email must end with @siswa.unimas.my',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'matric_no.required' => 'The matric number is required.',
            'matric_no.integer' => 'The matric number must be an integer.',
        ]);

        // Create the student with hashed password
        try {
            // Log before create attempt
            \Illuminate\Support\Facades\Log::info('Attempting to create student with matric_no:', [
                'matric_no' => $matricNo,
                'type' => gettype($matricNo)
            ]);
            
            // Create student record
            $student = new Student();
            $student->name = $credentials['name'];
            $student->email = $credentials['email'];
            $student->password = Hash::make($credentials['password']);
            $student->matric_no = $matricNo;
            $student->save();
            
            // Log successful creation
            \Illuminate\Support\Facades\Log::info('Student created successfully:', [
                'id' => $student->id,
                'matric_no' => $student->matric_no
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Student creation error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'matric_no' => $matricNo,
                'matric_no_type' => gettype($matricNo)
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'error_details' => [
                    'exception_type' => get_class($e),
                    'matric_no' => $matricNo,
                    'matric_no_type' => gettype($matricNo)
                ]
            ], 422);
        }

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
            /** @var \App\Models\Student $user */
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
            $query = Item::where('type', 'lost')->where('status', 'active');
            
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
            
            $items = $query->with(['category', 'color', 'location', 'student', 'claimLocation'])->get();
        }else if($type == 'found'){
            $query = Item::where('type', 'found')->where('status', 'active');
            
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
            
            $items = $query->with(['category', 'color', 'location', 'student', 'claimLocation'])->get();
        }else if($type == 'recovered'){
            // Get items with status 'resolved' instead of type 'recovered' 
            $query = Item::where('status', 'resolved')->where('type', 'found');
            
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
            
            $items = $query->with(['category', 'color', 'location', 'student', 'claimLocation'])->get();
        }else{
            $items = Item::with(['category', 'color', 'location', 'student', 'claimLocation'])->get();
        }

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    function getItemsByStudentId(Request $request){
        $studentId = $request->input('student_id');
        $type = $request->input('type');
        $items = Item::where('student_id', $studentId)
                    ->where('type', $type)
                    ->with(['category', 'color', 'location', 'claimLocation'])
                    ->get();
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

    public function getFaculties(){
        $faculties = \App\Models\Faculty::all();
        return response()->json([
            'success' => true,
            'faculties' => $faculties
        ]);
    }

    public function createItem(Request $request){
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|string|in:lost,found',
                'category_id' => 'required|exists:categories,id',
                'color_id' => 'required|exists:colours,id',
                'location_id' => 'required|exists:locations,id',
                'student_id' => 'required|exists:students,id',
                'claim_location_id' => 'nullable|exists:faculties,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $data = $request->all();
            
            // Handle image upload
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
            
            // Create the item (without embeddings for now)
            $item = Item::create($data);
            
            // Process image similarity if image was uploaded
            if ($request->hasFile('image')) {
                $imageSimilarityController = app(ImageSimilarityController::class);
                $similarityResult = $imageSimilarityController->processItemImage($request, $item);
                
                // Save embedding to the item
                if (isset($similarityResult['embedding']) && $similarityResult['embedding']) {
                    $item->image_embeddings = $similarityResult['embedding'];
                    $item->save();
                }
                
                // Create matches if any found with enough similarity
                if (isset($similarityResult['matches']) && !empty($similarityResult['matches'])) {
                    // The ImageSimilarityController now handles creating matches and sending notifications
                    
                    // Return with match information
                    return response()->json([
                        'success' => true,
                        'item' => $item,
                        'similarity_matches' => count($similarityResult['matches']),
                        'message' => 'Item created successfully with ' . count($similarityResult['matches']) . ' potential matches'
                    ]);
                }
            }
            
            // Return success if we got this far
            return response()->json([
                'success' => true,
                'item' => $item,
                'message' => 'Item created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getItemById($id){
        $item = Item::with(['category', 'color', 'location', 'student', 'claimLocation'])->find($id);
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

    public function checkItemRestriction(string $id){
        $item = Item::find($id);

        // Check if item can be edited based on matches and claims status
        //item can only be edited if it does not involve in any pending, approved, or rejected matches
        $hasRestrictedMatches = ItemMatch::where(function($query) use ($item) {
            $query->where('lost_item_id', $item->id)
                  ->orWhere('found_item_id', $item->id);
        })
        ->whereIn('status', ['pending', 'approved', 'rejected'])
        ->exists();

         // Check if item is involved in any claims (regardless of status)
         $hasAnyClaims = Claim::where('found_item_id', $item->id)
         ->orWhere('lost_item_id', $item->id)
         ->exists();

        if ($hasRestrictedMatches || $hasAnyClaims) {
            return response()->json([
                'success' => true,
                'can_be_edited' => false,
                'can_be_deleted' => false,
                'restrictedReason' => $hasRestrictedMatches ? 'This item cannot be edited because it is involved in pending, approved, or rejected matches' : 'This item cannot be edited because it is involved in claims'
            ]);
        }
    }

    public function updateItem(Request $request, string $id){
        try {
            $item = Item::findOrFail($id);
            
            // Check if item can be updated based on its status
            if ($item->status !== 'active') {
                Log::warning('Cannot update item: not in active status', ['id' => $id, 'status' => $item->status]);
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be updated because it is not in active status'
                ], 403);
            }
            
            // Restore original restriction checks
            // Check if item can be edited based on matches and claims status
            $hasRestrictedMatches = ItemMatch::where(function($query) use ($item) {
                    $query->where('lost_item_id', $item->id)
                          ->orWhere('found_item_id', $item->id);
                })
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->exists();
                
            if ($hasRestrictedMatches) {
                Log::warning('Cannot edit item: involved in matches with restricted status', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be edited because it is involved in pending, approved, or rejected matches'
                ], 403);
            }
            
            // Check if item is involved in any claims (regardless of status)
            $hasAnyClaims = Claim::where('found_item_id', $item->id)
                ->orWhere('lost_item_id', $item->id)
                ->exists();
                
            if ($hasAnyClaims) {
                Log::warning('Cannot edit item: involved in claims', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be edited because it is involved in claims'
                ], 403);
            }
            
            // Get the input data
            $input = [];
            
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
            
            if ($request->has('claim_location_id')) {
                $input['claim_location_id'] = $request->input('claim_location_id');
                $item->claim_location_id = $input['claim_location_id'];
                Log::info('Claim Location ID found in request', ['claim_location_id' => $input['claim_location_id']]);
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
                'claim_location_id' => 'sometimes|nullable|exists:faculties,id',
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
            
            // Track changes in category, color, or location
            $metadataChanged = false;
            $originalCategoryId = $item->getOriginal('category_id');
            $originalColorId = $item->getOriginal('color_id');
            $originalLocationId = $item->getOriginal('location_id');
            
            if ($originalCategoryId != $item->category_id || 
                $originalColorId != $item->color_id || 
                $originalLocationId != $item->location_id) {
                $metadataChanged = true;
                Log::info('Item metadata changed', [
                    'id' => $id,
                    'old_category' => $originalCategoryId,
                    'new_category' => $item->category_id,
                    'old_color' => $originalColorId,
                    'new_color' => $item->color_id,
                    'old_location' => $originalLocationId,
                    'new_location' => $item->location_id
                ]);
            }
            
            $imageChanged = false;
            $oldImage = $item->image;
            
            // Handle image upload if provided
            if ($request->hasFile('image')) {
                try {
                    $file = $request->file('image');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    
                    if($item->type == 'lost'){
                        $path = $file->storeAs('lost_items', $filename, 'public');
                    } else if($item->type == 'found'){
                        $path = $file->storeAs('found_items', $filename, 'public');
                    }
                    
                    $item->image = $filename;
                    $imageChanged = true;
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
                
                // Delete old image if a new one was uploaded
                if ($imageChanged && !empty($oldImage)){
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
                }
                
                // Process image with FastAPI if either the image was changed or metadata was changed
                if ($imageChanged || $metadataChanged) {
                    // First delete any existing matches to avoid duplicates
                    ItemMatch::where(function($query) use ($item) {
                        $query->where('lost_item_id', $item->id)
                              ->orWhere('found_item_id', $item->id);
                    })
                    ->whereIn('status', ['available', 'dismissed'])
                    ->delete();
                    
                    Log::info('Deleted existing matches for item', ['item_id' => $item->id]);
                    
                    // Get the image similarity controller
                    $imageSimilarityController = app(ImageSimilarityController::class);
                    
                    if ($imageChanged) {
                        // If image changed, process the new image to get embeddings and find matches
                        $result = $imageSimilarityController->processItemImage($request, $item);
                        
                        // Update the item with the new embedding
                        if (isset($result['embedding']) && !empty($result['embedding'])) {
                            $item->image_embeddings = $result['embedding'];
                            $item->save();
                        }
                        
                        // Send notifications based on matches
                        if (isset($result['matches']) && !empty($result['matches'])) {
                            Log::info('New matches found after image update', [
                                'item_id' => $item->id,
                                'matches_count' => count($result['matches'])
                            ]);
                            
                            // Individual match notifications are already sent by processItemImage -> createMatches
                            // No need for additional summary notification
                        } else if ($item->type === 'lost') {
                            // If no matches found for a lost item, send notification
                            $this->notificationService->sendNoMatchesNotification($item);
                            Log::info('No matches found after image update, notification sent', ['item_id' => $item->id]);
                        }
                    } else if ($metadataChanged && !$imageChanged && $item->image) {
                        // If only metadata changed but image exists, we need to find matches based on the existing image
                        // but with the new metadata
                        
                        // Create a modified request with the existing image
                        $modifiedRequest = new Request($request->all());
                        
                        // Get the image path
                        $imagePath = ($item->type === 'lost') 
                            ? storage_path('app/public/lost_items/' . $item->image)
                            : storage_path('app/public/found_items/' . $item->image);
                        
                        if (file_exists($imagePath)) {
                            // Create a UploadedFile instance from the existing file
                            // It's used to simulate a file upload when no new file was actually uploaded by the user. 
                            // The true parameter in the UploadedFile constructor indicates "test mode." In test mode, the file is not expected to be moved from its temporary upload location to a new destination. This is important here because:
                            // The file already exists in its final location
                            // It prevents the system from attempting to move the file during processing
                            // It tells Laravel this isn't a real upload from a form but a programmatically created file object
                            // Without this flag set to true, Laravel might try to move the file and could encounter errors since it's not actually in a temporary upload location.
                            $uploadedFile = new \Illuminate\Http\UploadedFile(
                                $imagePath,
                                $item->image,
                                mime_content_type($imagePath),
                                null,   //No specific file size is provided (will be determined automatically)
                                true
                            );
                            
                            // Add the file to the request
                            $modifiedRequest->files->set('image', $uploadedFile);
                            
                            // Process with the existing image but new metadata
                            $result = $imageSimilarityController->processItemImage($modifiedRequest, $item);
                            
                            // Update the item with the new embedding from the existing image
                            if (isset($result['embedding']) && !empty($result['embedding'])) {
                                $item->image_embeddings = $result['embedding'];
                                $item->save();
                                
                                Log::info('Image embeddings updated after metadata change', [
                                    'item_id' => $item->id
                                ]);
                            }
                            
                            // Send notifications based on matches
                            if (isset($result['matches']) && !empty($result['matches'])) {
                                Log::info('New matches found after metadata update', [
                                    'item_id' => $item->id,
                                    'matches_count' => count($result['matches'])
                                ]);
                                
                                // Individual match notifications are already sent by processItemImage -> createMatches
                                // No need for additional summary notification
                            } else if ($item->type === 'lost') {
                                // If no matches found for a lost item, send notification
                                $this->notificationService->sendNoMatchesNotification($item);
                                Log::info('No matches found after metadata update, notification sent', ['item_id' => $item->id]);
                            }
                        } else {
                            Log::warning('Image file not found for metadata-only update', [
                                'item_id' => $item->id,
                                'image_path' => $imagePath
                            ]);
                        }
                    }
                    
                    Log::info('Item processed for matches after update', [
                        'item_id' => $item->id,
                        'image_changed' => $imageChanged,
                        'metadata_changed' => $metadataChanged
                    ]);
                }
                
                Log::info('Item saved successfully', ['id' => $id]);
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
            
            // Check if item can be deleted based on matches and claims status
            $hasRestrictedMatches = ItemMatch::where(function($query) use ($item) {
                    $query->where('lost_item_id', $item->id)
                          ->orWhere('found_item_id', $item->id);
                })
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->exists();
                
            if ($hasRestrictedMatches) {
                Log::warning('Cannot delete item: involved in matches with restricted status', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be deleted because it is involved in pending, approved, or rejected matches'
                ], 403);
            }
            
            // Check if item is involved in any claims (regardless of status)
            $hasAnyClaims = Claim::where('found_item_id', $item->id)
                ->orWhere('lost_item_id', $item->id)
                ->exists();
                
            if ($hasAnyClaims) {
                Log::warning('Cannot delete item: involved in claims', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be deleted because it is involved in claims'
                ], 403);
            }
            
            // Delete any available or dismissed matches for this item
            ItemMatch::where(function($query) use ($item) {
                    $query->where('lost_item_id', $item->id)
                          ->orWhere('found_item_id', $item->id);
                })
                ->whereIn('status', ['available', 'dismissed'])
                ->delete();
            
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

    public function getPotentialMatches(int $itemId){
        $item = Item::find($itemId);
        
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }
        
        // Determine if this is a lost or found item to query the correct matches
        if ($item->type == 'lost') {
            $potentialMatches = ItemMatch::where('lost_item_id', $itemId)
                ->with([
                    'foundItem', 
                    'foundItem.category', 
                    'foundItem.color', 
                    'foundItem.location', 
                    'foundItem.student',
                    'foundItem.claimLocation'
                ])
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'matches' => $potentialMatches
        ]);
    }

    public function getStudentClaimsbyPotentialMatches(int $studentId, int $itemId){
        $item = Item::find($itemId);
        
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }
        
        // Determine if this is a lost or found item to query the correct matches
        if ($item->type == 'lost') {
            $claims = Claim::where('lost_item_id', $itemId)
                ->where('student_id', $studentId)
                ->with([
                    'foundItem', 
                    'foundItem.category', 
                    'foundItem.color', 
                    'foundItem.location', 
                    'foundItem.student', 
                    'foundItem.claimLocation',
                    'match'
                ])
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'claims' => $claims
        ]);
    }

    public function claimItem(Request $request){
        $data = $request->all();
        $claim = Claim::create($data);
        return response()->json([
            'success' => true,
            'claim' => $claim
        ]);
    }

    public function claimItemByMatch(Request $request){
        $data = $request->all();
        $claim = Claim::create($data);
        $match =ItemMatch::find($request->match_id);
        $match->status = 'pending';
        $match->save();

        //all other matches with status 'available' for this lost item should be dismissed
        ItemMatch::where('lost_item_id', $request->lost_item_id)
            ->where('status', 'available')
            ->update(['status' => 'dismissed']);
        
        
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
        $claims = Claim::where('student_id', $studentId)
                        ->with([
                            'foundItem',
                            'foundItem.category',
                            'foundItem.color',
                            'foundItem.location',
                            'foundItem.claimLocation'
                        ])
                        ->get();
        return response()->json([
            'success' => true,
            'claims' => $claims,
        ]);
    }

    public function getClaimDetails(int $id){
        $claim = Claim::with([
            'foundItem',
            'foundItem.category',
            'foundItem.color',
            'foundItem.location',
            'foundItem.claimLocation',
            'student',
            'admin'
        ])->find($id);

        return response()->json([
            'success' => true,
            'claim' => $claim
        ]);
    }

    public function getMatchingLostItemsWithScore(int $foundItemId){
        try {
            // Find the found item
            $foundItem = Item::findOrFail($foundItemId);
            
            // Check if this found item has an approved claim
            $approvedClaim = Claim::where('found_item_id', $foundItemId)
                                ->where(function($query) {
                                    $query->where('status', 'approved')
                                          ->orWhere('status', 'claimed');
                                })
                                ->first();
            
            if (!$approvedClaim) {
                return response()->json([
                    'success' => true,
                    'message' => 'No approved claim exists for this found item',
                    'lost_item' => null,
                    'similarity_score' => null
                ]);
            }
            
            // If the claim has a match_id, get the match to find similarity score
            $similarityScore = null;
            if ($approvedClaim->match_id) {
                $match = ItemMatch::find($approvedClaim->match_id);
                if ($match) {
                    $similarityScore = $match->similarity_score;
                }
            }
            
            // Get the lost item if it exists
            $lostItem = null;
            if ($approvedClaim->lost_item_id) {
                $lostItem = Item::with(['category', 'color', 'location', 'student'])
                                ->find($approvedClaim->lost_item_id);
            }
            
            return response()->json([
                'success' => true,
                'lost_item' => $lostItem ?? null,
                'similarity_score' => $similarityScore
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching matching lost item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch matching lost item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        // Validate request data
        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', // at least one uppercase letter
                'regex:/[a-z]/', // at least one lowercase letter
                'regex:/[0-9]/', // at least one number
                'regex:/[^a-zA-Z0-9]/' // at least one special character
            ],
            'password_confirmation' => 'required|string',
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        // Get the authenticated user
        $user = $request->user();
        
        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 401);
        }

        try {
            // Update the user's password
            $user->password = Hash::make($request->password);
            $user->save();
            
            // Log the password change
            \Illuminate\Support\Facades\Log::info('Password changed successfully for user', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password change failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changeUsername(Request $request){
        // Validate the request
        //  you can customize exactly how validation errors are returned to the client
        // format the JSON response with specific structure (success: false, custom HTTP status code 422)
         // With $request->validate(), the exception handler would determine the response format, which might not match the API's consistent error structure
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:students,name',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the authenticated user
            $user = $request->user();
            
            // Update the username (name field)
            $user->name = $request->name;
            $user->save();
            
            // Log the username change
            \Illuminate\Support\Facades\Log::info('Username changed successfully for user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'new_name' => $user->name
            ]);
            
            // Return the updated user object
            return response()->json([
                'success' => true,
                'message' => 'Username changed successfully',
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'matric_no' => $user->matric_no,
                'email_verified' => !is_null($user->email_verified_at),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Username change failed', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to change username: ' . $e->getMessage(),
            ], 500);
        }
    }
}
