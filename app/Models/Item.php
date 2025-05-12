<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    //
    protected $fillable = [
        'name',
        'description',
        'image',
        'image_embeddings',
        'type',
        'status',
        'category_id',
        'color_id',
        'location_id',
        'student_id',
    ];

    protected $casts =[
        'image_embeddings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Colour::class);
    }   

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // In app/Models/Item.php
    public function claims()
    {
        return $this->hasMany(Claim::class, 'found_item_id');
    }

    // For lost items specifically
    public function lostItemClaims()
    {
        return $this->hasMany(Claim::class, 'lost_item_id');
    }
    
    
       
}
