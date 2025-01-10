<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\support\Facades\DB;


use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Tag;
use App\Models\TagList;
use App\Models\SceneList;
use App\Models\Outfit;
use App\Models\Post;
use Illuminate\Auth\Events\Validated;

class OutfitController
{
    // 新增穿搭資料
    public function createOutfit(request $request)
    {

        // 驗證穿搭資訊
        $validatedOutfit = $request->validate([
            'Title' => 'required|string|max:8',
            'Content' => 'nullable|string|max:100',
            'Season' => 'nullable|string|max:10',
            'UID' => 'required|numeric|max:300',
            'EditedPhoto' => 'required|string',
            'filter' => 'required',
        ]);

        // return response(['test' => $validatedOutfit], 200);

        // 驗證場景資訊
        $validatedScene = $request->validate([
            'Scene' => 'nullable',
            'Scene.*' => 'nullable|string|max:10'
        ]);

        $tagList = $request->input('Tag');
        $tagComments = array_filter($tagList, fn($tag) => $tag['inCloset'] == 0);
        $tagItems = array_filter($tagList, fn($tag) => $tag['inCloset'] == 1);

        // 新增穿搭主表
        $outfit = Outfit::create([
            'Title' => $validatedOutfit['Title'],
            'Content' => $validatedOutfit['Content'],
            'Season' => $validatedOutfit['Season'],
            'EditedPhoto' => $validatedOutfit['EditedPhoto'],
            'UID' => $validatedOutfit['UID'],
            'FilterStyle' => $validatedOutfit['filter']
        ]);

        // 新增場景
        foreach ($validatedScene['Scene'] as $sceneName) {
            SceneList::create(
                [
                    'OutfitID' => $outfit['OutfitID'],
                    'SceneName' => $sceneName
                ]
            );
        }

        // 新增標籤（單品）
        foreach ($tagItems as $element) {
            TagList::create([
                'OutfitID' => $outfit['OutfitID'],
                'ItemID' => $element['itemID'],
                'X' => $element['x'],
                'Y' => $element['y'],
            ]);
        }

        // 新增標籤（註解）
        foreach ($tagComments as $element) {

            // 驗證每一個元素
            $validator = Validator::make($element, [
                'content' => 'required',
                'type' => 'nullable',
                'comment' => 'nullable',
                'size' => 'nullable',
                'brand' => 'nullable',
                'x' => 'nullable|numeric',
                'y' => 'nullable|numeric',
            ]);

            // 驗證失敗拋出訊息
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json(['errors' => $errors], 422);
            }

            // 寫入資料庫
            Tag::create([
                'OutfitID' => $outfit['OutfitID'],
                'Title' => $validator->validated()['content'],
                'Type' => $validator->validated()['type'],
                'Comment' => $validator->validated()['comment'],
                'Size' => $validator->validated()['size'],
                'Brand' => $validator->validated()['brand'],
                'X' => $validator->validated()['x'],
                'Y' => $validator->validated()['y']
            ]);
        }

        return response()->json($outfit['OutfitID'], 200);
    }

    // 查詢穿搭
    public function showOutfit($outfitID)
    {
        $outfit = Outfit::with(['scene', 'Items', 'tagInfo', 'tagComment'])->find($outfitID);

        if (!$outfit) {
            return response()->json(['message' => '找不到搭配'], 403);
        }
        return response()->json($outfit, 200);
    }

    // 取得使用者衣櫃單品
    public function showItems($UID)
    {
        $results = Item::join('Type', 'Item.Type', '=', 'Type.TypeID')
            ->where('UID', $UID)
            ->select('Title', 'Size', 'Brand', 'EditedPhoto', 'Name', 'PartID', 'ItemID')
            ->get();
        return response()->json($results);
    }


    // 更新穿搭
    public function updateOutfit(Request $request, $outfitID)
    {
        // 依據ID找資料
        $outfit = Outfit::find($outfitID);

        // 如果沒有找到
        if (!$outfit) {
            return response()->json(['message' => '找不到穿搭資料'], 404);
        }

        // 更新原有的資料
        $outfit->update($request->only(['Title', 'Content', 'Season']));

        // 處理場景（要先刪除、後新增）
        if ($request->has('Scene')) {
            // 先把原有的刪除
            SceneList::where('outfitID', $outfitID)->delete();

            // 建立資料
            foreach ($request->input('Scene') as $sceneName) {
                SceneList::create([
                    'OutfitID' => $outfitID,
                    'SceneName' => $sceneName
                ]);
            }
        }

        return response()->json(['message' => '更新成功'], 200);
    }

    // 刪除穿搭
    public function deleteOutfit($outfitID)
    {
        // 刪除標籤資料
        Tag::where('OutfitID', $outfitID)->delete();
        
        $outfitData = Outfit::find($outfitID);
        

        if (!$outfitData) {
            return response()->json(['message' => '沒有找到資料'], 404);
        }

        // 刪除多對多關聯
        $outfitData->Items()->detach();

        // 刪除一對多關聯
        $outfitData->scene()->delete();
        $outfitData->tagInfo()->delete();

        // 刪除穿搭牆資料
        Post::where('OutfitID', $outfitID)->delete();
        
        $outfitData->delete();

        return response()->json(['message' => 'Outfit 已成功刪除'], 200);
    }
}
