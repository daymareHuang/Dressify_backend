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
Route::post('/getmenpost', [WallController::class, 'getmenpost']);

// 拿女人的時間最晚的五則po文
Route::post('/getwomenpost', [WallController::class, 'getwomenpost']);

// 搜尋頁面
// 搜尋
Route::post('/search', [WallController::class, 'search']);

// 複雜搜尋
Route::post('/complicatedsearch', [WallController::class, 'complicatedsearch']);

// 抓衣服品牌
Route::get('/brand', [WallController::class, 'brand']);

// 抓衣服類別
Route::get('/clothestype', [WallController::class, 'clothestype']);

// 搜尋衣服ID
Route::post('/getClothesTypeID', [WallController::class, 'getClothesTypeID']);


// 使用者個人頁面
// 抓使用者post
Route::post('/getuserpost', [WallController::class, 'getuserpost']);

// 抓使用者collect
Route::post('/getusercollect', [WallController::class, 'getusercollect']);

// 抓貼文數
Route::post('/getpostnum', [WallController::class, 'getpostnum']);

//抓粉絲數
Route::post('/getfannum',[WallController::class, 'follownum']);

// userinfo 的所有api
// 能夠抓取user 所有的資料 利用上面的api去做
// 要放在selfpage裡面
Route::post('/userself', [WallController::class, 'userself']);

// 抓其他使用者的資訊
Route::post('/otherppl',[WallController::class, 'otherppl']);

// 讓使用者可以追蹤
Route::post('/follow',[WallController::class, 'follow']);

// 讓使用者可以退追蹤
Route::post('/unfollow',[WallController::class, 'unfollow']);

Route::post('/checkfollow',[WallController::class, 'followcheck']);


// ====================我是分隔線======================
// 小雁's api：
use App\Http\Controllers\ItemController;
// 取得user的所有items (v)
Route::get('/items/{UID}', [ItemController::class, 'userItem']);

// 新增一筆item (v)
Route::post('/item', [ItemController::class, 'createItem']);

// 查詢一筆item的所有info (v)
Route::get('/item/{ItemID}', [ItemController::class, 'itemInfo']);

// 修改一筆item (v)
Route::put('/item/{ItemID}', [ItemController::class, 'updateItem']);

// 刪除一筆item (v)
Route::delete('/item/{ItemID}', [ItemController::class, 'deleteItem']);

// 取得user的所有outfit (v)
Route::get('/outfits/{UID}', [ItemController::class, 'userOutfit']);

// 關鍵字搜尋item - v2 (v)
Route::get('/items/{UID}/search', [ItemController::class, 'searchItem']);

// 單品有在哪些outfit中被使用ㄉapi (v) - UID =/= 1可再double-check
Route::get('/item/{ItemID}/outfits', [ItemController::class, 'itemOutfit']);

// 單品有哪些相似的穿搭可以在dresswall被看到 (v) - UID =/= 1可再double-check
Route::get('/item/{ItemID}/{UID}/recomms', [ItemController::class, 'itemRecomms']);


// ====================我是分隔線======================
// 田
use App\Models\Item;
use App\Http\Controllers\OutfitController;

Route::get('/closetType', [OutfitController::class, 'searchTypeItem']);

// 新增穿搭
Route::post('/OutfitDescription', [OutfitController::class, 'createOutfit']);

// 撈單品資料
Route::get('/closet', function () {
    $results = Item::join('Type', 'Item.Type', '=', 'Type.TypeID')
        ->select('Title', 'Size', 'Brand', 'EditedPhoto', 'Name', 'PartID', 'ItemID')
        ->get();
    return response()->json($results);
});

// 取得使用者衣櫃單品
Route::get('closet/{UID}',[OutfitController::class,'showItems']);

// 查詢穿搭資訊
Route::get('/ClosetMatch/{outfitID}', [OutfitController::class, 'showOutfit']);

// 更新穿搭資訊
Route::patch('/ClosetMatch/{outfitID}', [OutfitController::class, 'updateOutfit']);

// 刪除穿搭資訊
Route::delete('/ClosetMatch/{outfitID}', [OutfitController::class, 'deleteOutfit']);

// 發文
Route::post('/PostPost',[WallController::class, 'postPost']);


// ====================我是分隔線======================


// ====================我是分隔線======================
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

// 選擇個人穿搭
Route::get('/outfits/photos/{UID}', function ($UID) {
    $outfits = DB::table('outfit')
        ->select('UID', 'EditedPhoto')
        ->where('UID', $UID)  //    篩選指定 UID 的照片
        ->get();
    return response()->json($outfits);
});

// 取得不符目前登入UID的穿搭
Route::get('/outfits/photos/exceptFor/{UID}', function ($UID) {
    $outfits = DB::table('outfit')
        ->select('UID', 'EditedPhoto') // 選擇需要的欄位
        ->where('UID', '!=', $UID) // 篩選不包含指定 UID 的照片
        ->get();
    return response()->json($outfits);
});

// 取得member資料
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

// 處理頭像上傳
Route::post('/update-avatar/{UID}', function (Request $request, $UID) {
    // 獲取前端頭像資料
    $avatar = $request->input('avatar');

    if ($avatar) {
        // 更新指定 UID 的會員頭像
        $updated = DB::table('member')
            ->where('UID', $UID)
            ->update(['avatar' => $avatar]);

        if ($updated) {
            // 更新成功時，同步寫入資料庫及刷新頁面
            // return redirect()->back()->with('success', '头像更新成功');
            $member = DB::table('member')->where('UID', $UID)->first();
            return response()->json([
                'success' => true,
                'message' => '头像更新成功',
                'data' => $member
            ]);
        }
    }
});
