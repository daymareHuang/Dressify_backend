<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\support\Facades\DB;

use App\Http\Controllers\WallController;


// 用這個方法get 去到
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function (Request $request) {
    return response('{"acknowledged": true}')
        ->header('content-type', 'application/json')
        ->header('charset', 'utf-8');
});


// 先全部get 到最後改成post

// post 頁面的所有api

// 穿搭強的頁面
// 這個api 是能夠當使用者按讚的時候 傳給我們他所按讚的貼文ID(???)
// 以及我們必須自己去找當時登入的人是誰 他的ID(???)
Route::post('/like', [WallController::class, 'like']);

// 能夠取消當時登入的人他所按讚貼文的讚
Route::post('/unlike', [WallController::class, 'unlike']);

// 能夠 能夠當使用者收藏的時候 傳給我們他所蒐藏的貼文ID(???)
// 以及我們必須自己去找當時登入的人是誰 他的ID(???)
Route::post('/collect', [WallController::class, 'collect']);

// 能夠取消當時登入的人他所蒐藏的蒐藏
Route::post('/uncollect', [WallController::class, 'uncollect']);


// 能夠取得 (__、依時間最晚發?)的五則貼文
Route::get('/getmenpost', [WallController::class, 'getmenpost']);

// 拿女人的時間最晚的五則po文
Route::get('/getwomenpost', [WallController::class, 'getwomenpost']);

// 搜尋頁面
// 搜尋
Route::post('/search', [WallController::class, 'search']);

// 複雜搜尋
Route::post('/complicatedsearch',[WallController::class, 'complicatedsearch'] );

// 抓衣服品牌
Route::get('/brand', [WallController::class, 'brand']);

// 抓衣服類別
Route::get('/clothestype', [WallController::class, 'clothestype']);


// 使用者個人頁面
// 抓使用者post
Route::post('/getuserpost', [WallController::class, 'getuserpost']);

// 抓使用者collect
Route::post('/getusercollect', [WallController::class, 'getusercollect']);

// 抓貼文數
Route::post('/getpostnum', [WallController::class, 'getpostnum']);


// userinfo 的所有api
// 能夠抓取user 所有的資料 利用上面的api去做
// 要放在selfpage裡面
Route::post('/userself', [WallController::class, 'userself']);



// 抓粉絲數 沒有table???<?php

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