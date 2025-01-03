<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagList extends Model
{
    public $timestamps = false; // 取消timestamps
    protected $table = 'TagList';
    protected $fillable = ['OutfitID', 'ItemID','X','Y'];

    public function outfit(){
        return $this->belongsTo(Outfit::class,'OutfitID','OutfitID');
    }
}
