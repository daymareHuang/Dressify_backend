<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagList extends Model
{
    public $timestamps = false; // 取消timestamps
    protected $table = 'TagList';
    protected $fillable = ['OutfitID', 'ItemID', 'X', 'Y'];

    // 關聯 Item
    public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID', 'ItemID');
    }
    
    // 關聯 Outfit
    public function outfit()
    {
        return $this->belongsTo(Outfit::class, 'OutfitID', 'OutfitID');
    }

}