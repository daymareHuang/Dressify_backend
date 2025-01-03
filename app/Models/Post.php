<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'Post';
    protected $primaryKey = 'PostID';
    public $timestamps = false;

    protected $fillable = ['PostID', 'OutfitID'];

    public function outfit() {
        return $this->belongsTo(Outfit::class, 'OutfitID', 'OutfitID');
    }
}
