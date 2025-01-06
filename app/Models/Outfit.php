<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outfit extends Model
{
    protected $table = 'Outfit';
    protected $primaryKey = 'OutfitID';
    public $timestamps = false;

    protected $fillable = ['UID', 'Title', 'Content', 'Season', 'EditedPhoto','FilterStyle'];

    // 定義與 Item 的多對多關聯
    public function items()
    {
        return $this->belongsToMany(Item::class, 'TagList', 'OutfitID', 'ItemID');
    }

    // 定義與 post 的一對多關係
    public function posts()
    {
        return $this->hasMany(Post::class, 'OutfitID', 'OutfitID');
    }

    // 定義多對一關係
    public function member()
    {
        return $this->belongsTo(Member::class, 'UID', 'UID');
    }

    public function scene()
    {
        return $this->hasMany(SceneList::class, 'OutfitID', 'OutfitID');
    }


    public function tagInfo()
    {
        return $this->hasMany(TagList::class, 'OutfitID', 'OutfitID');
    }

    // 定義與 Tag 的一對多關係
    public function tagComment(){
        return $this->hasMany(Tag::class,'OutfitID','OutfitID');
    }
}
