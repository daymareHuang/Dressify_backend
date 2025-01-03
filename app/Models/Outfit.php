<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outfit extends Model
{
    protected $table = 'Outfit';
    protected $primaryKey = 'OutfitID';
    public $timestamps = false;

    protected $fillable = ['UID', 'Title', 'Content', 'Season', 'EditedPhoto'];

    // 定義與 Item 的多對多關聯
    public function items()
    {
        return $this->belongsToMany(Item::class, 'TagList', 'OutfitID', 'ItemID');
    }

    // 定義與 post 的一對多關係
    public function posts() {
        return $this->hasMany(Post::class, 'OutfitID', 'OutfitID');
    }

    // 定義多對一關係
    public function member() {
        return $this->belongsTo(Member::class, 'UID', 'UID');
    }
}
