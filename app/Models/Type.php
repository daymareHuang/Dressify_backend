<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'Type';
    protected $primaryKey = 'TypeID';
    public $timestamps = false;

    protected $fillable = ['Name', 'PartID'];

    // 定義與 Item 模型的一對多關聯
    public function items()
    {
        return $this->hasMany(Item::class, 'Type', 'TypeID');
    }
}
