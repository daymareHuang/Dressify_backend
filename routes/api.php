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




// 琇雁
use App\Models\Outfit;
// 瀏覽所有items
Route::get('/items', function () {
    $items = Item::with('type')->get();
    return $items;
});

// 新增一筆item～
Route::post('item', function (Request $request) {
    $validated = $request->validate([
        'UID' => 'required|int|max:20',
        'Title' => 'required|string|max:8',
        'Type' => 'required|int|max:36',
        'Color' => 'nullable|string|max:5',  // 要記得同步更新item.php裡的$fillable！！ => okie
        'Size' => 'nullable|string|max:20',
        'Brand' => 'nullable|string|max:50',
        'EditedPhoto' => 'nullable|string'
    ]);
    return Item::create($validated);
});

// 修改一筆item～
Route::put('item/{ItemID}', function (Request $request, $ItemID) {
    $item = Item::findOrFail($ItemID);
    $validated = $request->validate([
        'Title' => 'required|string|max:8',
        'Type' => 'required|int|max:36',
        'Color' => 'nullable|string|max:5',
        'Size' => 'nullable|string|max:20',
        'Brand' => 'nullable|string|max:50',
        // 'EditedPhoto' => 'nullable|string'  (暫時沒有打算讓使用者更新的時候重新上傳圖片><)
    ]);
    return $item->update($validated);  // 成功結果為1，
});

// 查詢一筆item的所有info～
Route::get('item/{ItemID}', function ($ItemID) {
    return Item::with('type')->findOrFail($ItemID);
});

// 查詢所有outfit～
Route::get('/outfits', function () {
    return Outfit::all();
});

// 搜尋Item中有沒有相似的單品（by keyword）～
// 之後也許要同時搜尋Outfit資料表？
Route::get('items/search', function (Request $request) {
    // 取得使用者輸入的關鍵字
    $keyword = $request->input('keyword');

    // 檢查是否有提供 keyword
    if (!$keyword) {
        return response()->json(['message' => '請提供搜尋關鍵字'], 400);
    }

    // 將多個關鍵字分割
    $keywords = explode(' ', $keyword);

    // 查詢多個欄位 ＊可加上color
    $items = Item::where(function ($query) use ($keywords) {
        foreach ($keywords as $word) {
            $query->orWhere('Title', 'LIKE', "%$word%")
                ->orWhere('Brand', 'LIKE', "%$word%")
                ->orWhere('Size', 'LIKE', "%$word%");
        }
    })
        ->orWhereHas('type', function ($query) use ($keywords) {
            foreach ($keywords as $word) {
                $query->where('Name', 'LIKE', "%$word%");
            }
        })

        ->with('type')  // 載入關聯的 type 資料
        ->take(5)  // 限制取回筆數只有５筆
        ->get();

    // 回傳結果
    return $items;
});