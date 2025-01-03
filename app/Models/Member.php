<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    // 指定資料表名稱
    protected $table = 'Member';

    // 定義主鍵
    protected $primaryKey = 'UID';
    public $timestamps = false;

    // // 指定主鍵是否自動遞增
    // public $incrementing = false;

    // // 指定主鍵的型別
    // protected $keyType = 'string';

    // 指定可以被批量賦值的欄位
    protected $fillable = [
        'UID',
        'UserName',
        'Avatar',
    ];

    // 關聯到 Outfit
    public function outfits()
    {
        return $this->hasMany(Outfit::class, 'UID', 'UID');
    }

    // 關聯到 Post（如果需要）
    // public function posts()
    // {
    //     return $this->hasManyThrough(Post::class, Outfit::class, 'UID', 'OutfitID', 'UID', 'OutfitID');
    // }
}
