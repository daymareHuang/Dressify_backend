<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
// use Illuminate\Support\Facades\Auth;
use App\Models\Outfit;
use App\Models\Post;
use App\Models\TagList;

class ItemController extends Controller
{
    // 取得user的所有items
    public function userItem($UID)
    {
        $items = Item::where('UID', $UID)->with('type')->get();
        return response()->json($items);
    }

    // 新增一筆item
    public function createItem(Request $request)
    {
        $validated = $request->validate([
            'UID' => 'required|int|max:20',
            'Title' => 'required|string|max:8',
            'Type' => 'required|int|max:37',
            'Color' => 'nullable|string|max:5',
            'Size' => 'nullable|string|max:20',
            'Brand' => 'nullable|string|max:50',
            'EditedPhoto' => 'nullable|string'
        ]);


        $item = Item::create($validated);
        return response()->json($item, 201);
    }

    // 查詢一筆item的所有info
    public function itemInfo($ItemID)
    {
        $item = Item::with('type')->findOrFail($ItemID);
        return response()->json($item);
    }

    // 修改一筆item
    public function updateItem(Request $request, $ItemID)
    {
        $item = Item::findOrFail($ItemID);
        $validated = $request->validate([
            'Title' => 'required|string|max:8',
            'Type' => 'required|int|max:37',
            'Color' => 'nullable|string|max:5',
            'Size' => 'nullable|string|max:20',
            'Brand' => 'nullable|string|max:50',
            // 'EditedPhoto' => 'nullable|string'  (暫時沒有打算讓使用者更新的時候重新上傳圖片><)
        ]);
        return $item->update($validated);  // 成功結果為1
    }

    // 刪除一筆item
    public function deleteItem($ItemID)
    {
        Item::findOrFail($ItemID)->delete();
        return response()->json(['status' => 'succeed']);
    }

    // 取得user的所有outfit
    public function userOutfit($UID)
    {
        $outfits = Outfit::where('UID', $UID)->get();
        return response()->json($outfits);
    }

    // 關鍵字搜尋item - v2
    public function searchItem(Request $request, $UID)
    {
        $keyword = $request->input('keyword');

        if (!$keyword) {
            return response()->json(['message' => '請提供搜尋關鍵字'], 400);
        }

        // 將多個關鍵字分割
        $keywords = explode(' ', $keyword);

        $items = Item::where('UID', $UID)->where(function ($query) use ($keywords) {
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
            // ->orderByRaw("FIELD(Title, ?)", [$keyword]) // 聚焦排序（把最相關的放前面）
            ->take(5)  // 限制取回筆數只有 5 筆
            ->get();

        return $items;
    }

    // 單品有在哪些outfit中被使用ㄉapi
    public function itemOutfit($ItemID)
    {
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
    }

    // 單品有哪些相似的穿搭可以在dresswall被看到
    public function itemRecomms($ItemID, $UID) {
        // 假設已經從登入用戶的 Session 或 Token 取得 UID
        $currentUID = $UID;  // 取得當前使用者的UID
    
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
    }
}
