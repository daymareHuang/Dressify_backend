<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // 確保綁定到正確的資料表
    protected $table = 'Item';
    // 指定主鍵名稱
    protected $primaryKey = 'ItemID';
    // 綁定可以被異動的欄位
    protected $fillable = ['UID', 'Title', 'Type', 'Size', 'Brand', 'EditedPhoto', 'Color'];
    // 取消預設會寫入兩筆timestampsㄉ欄位！
    public $timestamps = false;

    // 定義與 Type 模型的多對一關聯
    public function type()
    {
        return $this->belongsTo(Type::class, 'Type', 'TypeID');
    }

    // 定義 與 Outfit 的多對多關聯
    public function outfits()
    {
        return $this->belongsToMany(Outfit::class, 'TagList', 'ItemID', 'OutfitID');
    }
}
