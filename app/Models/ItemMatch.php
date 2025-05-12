<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemMatch extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'match_id',
        'lost_item_id',
        'found_item_id',
        'similarity_score',
        'status',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matches';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the item associated with the claim.
     */
    // public function item(): BelongsTo
    // {
    //     return $this->belongsTo(Item::class);
    // }

    public function lostItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'lost_item_id');
    }

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'found_item_id');
    }
}