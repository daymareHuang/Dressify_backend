<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Item;
use App\Http\Controllers\OutfitController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// 田
Route::get('/closetType', [OutfitController::class, 'searchTypeItem']);

// 新增穿搭
// Route::post('/OutfitDescription', [OutfitController::class, 'addOutfit']);
Route::post('/OutfitDescription', [OutfitController::class, 'createOutfit']);

// 撈單品資料
Route::get('/closet', function () {
    $results = Item::join('Type', 'Item.Type', '=', 'Type.TypeID')
        ->select('Title', 'Size', 'Brand', 'EditedPhoto', 'Name', 'PartID', 'ItemID')
        ->get();
    return response()->json($results);
});

// 查詢穿搭資訊
Route::get('/ClosetMatch/{outfitID}',[OutfitController::class,'showOutfit']);

// 更新穿搭資訊
Route::patch('/ClosetMatch/{outfitID}',[OutfitController::class,'updateOutfit']);

// 刪除穿搭資訊
Route::delete('/ClosetMatch/{outfitID}',[OutfitController::class,'deleteOutfit']);