<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outfit extends Model
{
    public $timestamps = false; // 取消timestamps
    protected $primaryKey = 'OutfitID';
    protected $table = 'Outfit'; //定義資料表名稱
    protected $fillable = ['Title', 'Content', 'Season', 'EditedPhoto','UID']; // 定義資料表欄位

    public function scene (){
        return $this->hasMany(SceneList::class, 'OutfitID', 'OutfitID');
    }

    public function Items(){
        return $this->belongsToMany(Item::class,'TagList','OutfitID','ItemID');
    }

    public function tagInfo(){
        return $this->hasMany(TagList::class,'OutfitID','OutfitID');
    }
}
