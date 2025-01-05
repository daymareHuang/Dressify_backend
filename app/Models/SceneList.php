<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SceneList extends Model
{
    public $timestamps = false; // 取消timestamps
    protected $table= 'SceneList';
    protected $fillable = ['OutfitID','SceneName'];
    public function outfit(){
        $this->belongsTo('Outfit','OutfitID','OutfitID');
    }
}
