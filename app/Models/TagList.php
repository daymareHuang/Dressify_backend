<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagList extends Model
{
    // 指定對應的資料表
    protected $table = 'TagList';

    // 允許批量賦值的欄位
    protected $fillable = [
        'ItemID',
        'OutfitID',
    ];

    // 關聯 Item
    public function item() {
        return $this->belongsTo(Item::class, 'ItemID', 'ItemID');
    }

    // 關聯 Outfit
    public function outfit() {
        return $this->belongsTo(Outfit::class, 'OutfitID', 'OutfitID');
    }
}
