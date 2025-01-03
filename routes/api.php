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



// 抓粉絲數 沒有table???



// ====================我是分隔線======================
// 小雁's api：
use App\Models\Item;
// 瀏覽所有items
Route::get('/items', function () {
    $items = Item::with('type')->get();
    return $items;
});

// 新增一筆item～
Route::post('/item', function (Request $request) {
    $validated = $request->validate([
        'UID' => 'required|int|max:20',
        'Title' => 'required|string|max:8',
        'Type' => 'required|int|max:37',
        'Color' => 'nullable|string|max:5',  // 要記得同步更新item.php裡的$fillable！！ => okie
        'Size' => 'nullable|string|max:20',
        'Brand' => 'nullable|string|max:50',
        'EditedPhoto' => 'nullable|string'
    ]);
    return Item::create($validated);
});

// 查詢一筆item的所有info～
Route::get('/item/{ItemID}', function ($ItemID) {
    return Item::with('type')->findOrFail($ItemID);
});

// 修改一筆item～
Route::put('/item/{ItemID}', function (Request $request, $ItemID) {
    $item = Item::findOrFail($ItemID);
    $validated = $request->validate([
        'Title' => 'required|string|max:8',
        'Type' => 'required|int|max:37',
        'Color' => 'nullable|string|max:5',
        'Size' => 'nullable|string|max:20',
        'Brand' => 'nullable|string|max:50',
        // 'EditedPhoto' => 'nullable|string'  (暫時沒有打算讓使用者更新的時候重新上傳圖片><)
    ]);
    return $item->update($validated);  // 成功結果為1，
});

// 刪除一筆item～
Route::delete('/item/{ItemID}', function ($ItemID) {
    Item::findOrFail($ItemID)->delete();
    return response()->json(['status' => 'succeed']);
});

use App\Models\Outfit;
// 查詢所有outfit～
Route::get('/outfits', function () {
    return Outfit::all();
});

// 搜尋item - v2
Route::get('items/search', function (Request $request) {
    $keyword = $request->input('keyword');

    if (!$keyword) {
        return response()->json(['message' => '請提供搜尋關鍵字'], 400);
    }

    // 將多個關鍵字分割
    $keywords = explode(' ', $keyword);

    $items = Item::where(function ($query) use ($keywords) {
        // 對每個關鍵字進行搜尋
        foreach ($keywords as $word) {
            $query->orWhere('Title', 'LIKE', "%$word%")
                  ->orWhere('Color', 'LIKE', "%$word%")
                  ->orWhere('Brand', 'LIKE', "%$word%")
                  ->orWhere('Size', 'LIKE', "%$word%");
        }
    })
    ->where(function ($query) use ($keywords) {
        // 聚焦：要求所有關鍵字至少在 `Title` 中匹配一次
        foreach ($keywords as $word) {
            $query->where('Title', 'LIKE', "%$word%");
        }
    })
    ->orWhereHas('type', function ($query) use ($keywords) {
        // 檢查關聯的類型名稱是否包含所有關鍵字
        foreach ($keywords as $word) {
            $query->where('Name', 'LIKE', "%$word%");
        }
    })
    ->with('type')  // 載入關聯的 type 資料
    ->orderByRaw("FIELD(Title, ?)", [$keyword]) // 聚焦排序（把最相關的放前面）
    ->take(5)  // 限制取回筆數只有 5 筆
    ->get();

    return $items;
});

// 單品有在哪些outfit中被使用ㄉapi
Route::get('/item/{ItemID}/outfits', function ($ItemID) {
    // 查找指定 Item 的所有相關 Outfit
    $item = Item::with('outfits.items') // 同時載入相關的 Outfit 和 Outfit 中的其他 Items
        ->findOrFail($ItemID);

    $relatedOutfits = $item->outfits->map(function ($outfit) {
        return [
            'OutfitID' => $outfit->OutfitID,
            'OutfitTitle' => $outfit->Title,
            'ItemsInOutfit' => $outfit->items->map(function ($relatedItem) {
                return [
                    'ItemID' => $relatedItem->ItemID,
                    // 'Title' => $relatedItem->Title,
                    'EditedPhoto' => $relatedItem->EditedPhoto,
                ];
            }),
        ];
    });

    return response()->json($relatedOutfits);
});

use App\Models\Post;
use App\Models\TagList;
// 單品有哪些相似的穿搭可以在dresswall被看到
Route::get('/item/{ItemID}/recomms', function ($ItemID) {
    // 假設已經從登入用戶的 Session 或 Token 取得 UID
    // (Request $request)
    // $currentUID = $request->user()->UID;
    $currentUID = 1;  // 測試用 UID，正式應從認證機制獲取

    // 找到該單品
    $item = Item::findOrFail($ItemID);

    // 使用該單品的 Title、Size、Brand 等條件來搜尋相似單品
    $similarItems = Item::where('ItemID', '!=', $item->ItemID)
        ->where(function ($query) use ($item) {
            $query->where('Title', 'LIKE', '%' . $item->Title . '%')
                // ->orWhere('Color', $item->Color)  // 因為目前很多顏色都是nullＱＱ
                ->orWhere('Size', $item->Size)
                ->orWhere('Brand', $item->Brand);
        })
        ->get();

    // 找到這些單品相關的 Outfit
    $outfitIds = TagList::whereIn('ItemID', $similarItems->pluck('ItemID'))
        ->distinct()  // 確保資料不重複
        ->pluck('OutfitID');
    // ->unique();

    // 過濾出 UID 不等於當前用戶的 Outfit，並載入 Member 資料
    $outfits = Outfit::whereIn('OutfitID', $outfitIds)
        ->where('UID', '!=', $currentUID)
        ->with(['member' => function ($query) {
            $query->select('UID', 'UserName', 'Avatar');
        }])
        ->get();

    // 找到符合條件的 Post 並載入相關的 Outfit
    $posts = Post::whereIn('OutfitID', $outfits->pluck('OutfitID'))
        ->with(['outfit.member' => function ($query) {
            $query->select('UID', 'UserName', 'Avatar'); // 確保 Post 關聯的 Outfit 也載入 Member的三個欄位
        }])
        ->get();

    return response()->json([
        'similar_items' => $similarItems,
        'outfit_ids' => $outfitIds,
        'posts' => $posts,
    ]);
});


// 田
use App\Http\Controllers\OutfitController;
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


use App\Models\Member;
use App\Http\Controllers\AuthController;

// 註冊
Route::post('register', [AuthController::class, 'register']);

// 登入
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth'])->group(function () {
    // 顯示導覽頁
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // 顯示修改會員資料頁
    Route::get('/modification', [AuthController::class, 'index'])->name('modification');
    
    // 更新會員資料
    Route::put('update-profile', [AuthController::class, 'updateProfile']);
    
    // 刪除帳號
    Route::delete('delete-account', [AuthController::class, 'deleteAccount']);
    
    // 登出
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('/outfits/photos', function () {
    $outfits = DB::table('outfit')->select('UID', 'outfitID', 'EditedPhoto')->get();
    return response()->json($outfits);
});

Route::get('user-info/{uid}', function ($UID) {
    $member = Member::where('uid', $UID)->first();
    if ($member) {
        // 返回使用者資料（UserName 和 Avatar）
        return response()->json([
            'UserName' => $member->UserName,  // 確保欄位名稱與資料庫一致
            'Avatar' => $member->Avatar       // 同樣欄位名稱正確
        ]);
    } else {
        // 如果沒有會員資料，返回 404 或錯誤信息
        return response()->json(['message' => 'No user data found'], 404);
    }
});
