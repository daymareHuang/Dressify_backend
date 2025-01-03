<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;


use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Tag;
use App\Models\TagList;
use App\Models\SceneList;
use App\Models\Outfit;
use Illuminate\Auth\Events\Validated;

class OutfitController
{
    // 新增穿搭資料
    public function addOutfit(request $request)
    {
        $validatedOutfit = $request->validate([
            'Title' => 'required|string|max:8',
            'Content' => 'nullable|string|max:100',
            'UID' => 'required|numeric',
            'EditedPhoto' => 'nullable|string',
        ]);
        //寫入穿搭資料表
        $outfit = Outfit::create($validatedOutfit);

        // 新增場景
        $sceneData = $request->validate([
            'Scene' => 'required|array',
            'Scene.*' => 'nullable|string|max:5'
        ]);
        foreach ($sceneData['Scene'] as $sceneItem) {
            SceneList::create([
                'OutfitID' => $outfit['OutfitID'],
                'Scene' => $sceneItem,
            ]);
        }

        $tagList = $request->input('Tag');

        // 新增標籤（衣櫃單品）
        $tagItemList = array_filter($tagList, fn($tag) => $tag['inCloset'] === 1);
        foreach ($tagItemList as $tagItem) {
            // 驗證資料
            $validator = Validator::make($tagItem, [
                'itemID' => 'nullable|numeric',
                'x' => 'nullable',
                'y' => 'nullable'
            ]);

            // 拋出錯誤
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json(['errors' => $errors], 422);
            }

            $tagItem = TagList::create([
                'OutfitID' => $outfit['OutfitID'],
                'ItemID' => $validator->validated()['itemID'],
                'X' => $validator->validated()['x'],
                'Y' => $validator->validated()['y'],
            ]);
        }

        // 新增標籤（註解）
        $tagCommentList = array_filter($tagList, fn($tag) => $tag['inCloset'] == 0);
        foreach ($tagCommentList as $tagItem) {

            $validator = Validator::make($tagItem, [
                'content' => 'required',
                'type' => 'nullable',
                'comment' => 'nullable',
                'size' => 'nullable',
                'brand' => 'nullable',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json(['errors' => $errors], 422);
            }

            Tag::create([
                'OutfitID' => $outfit['OutfitID'],
                'Title' => $validator->validated()['content'],
                'Type' => $validator->validated()['type'],
                'Comment' => $validator->validated()['comment'],
                'Size' => $validator->validated()['size'],
                'Brand' => $validator->validated()['brand'],
            ]);
        }

        // return response()->json(['message'=>'已寫入穿搭資料','outfitData'=>$outfit, 'tagData'=>$tag, 'sceneList'=>$senseListData],201);
        return response()->json(['info' => ''], 201);
    }

    public function createOutfit(request $request)
    {

        $validatedOutfit = $request->validate([
            'Title' => 'required|string|max:8',
            'Content' => 'nullable|string|max:100',
            'Season' => 'nullable|string|max:10',
            'UID' => 'required|numeric|max:300',
            'EditedPhoto' => 'required|string',
        ]);

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
            'UID' => 1,
        ]);

        // 新增場景
        foreach ($validatedScene['Scene'] as $sceneName) {
            $outfit->scene()->create([
                'Scene' => $sceneName,
            ]);
        }

        // 新增標籤（單品）
        foreach ($tagItems as $element) {
            $outfit->Items()->attach([
                $element['itemID'] => [
                    'X' => $element['x'],
                    'Y' => $element['y'],
                ]
            ]);
        }

        foreach($tagComments as $element){
            
        }

        return response(['data' => $tagItems], 200);
    }

    // 查詢穿搭
    public function showOutfit($outfitID)
    {
        $outfit = Outfit::with(['scene', 'Items', 'tagInfo'])->find($outfitID);

        if (!$outfit) {
            return response()->json(['message' => '找不到搭配'], 403);
        }
        return response()->json($outfit, 200);
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
                    'Scene' => $sceneName
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

        $outfitData->delete();

        return response()->json(['message' => 'Outfit 已成功刪除'], 200);
    }
}
