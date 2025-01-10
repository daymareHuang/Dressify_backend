<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\support\Facades\DB;



class WallController extends Controller
{

    // 穿搭強的頁面
// 這個api 是能夠當使用者按讚的時候 傳給我們他所按讚的貼文ID(???)
// 以及我們必須自己去找當時登入的人是誰 他的ID(???)
    public function like(Request $request)
    {
        $UID = $request->UID;
        $PostID = $request->PostID;
        DB::insert('insert into LikeTable (UID, PostID) values (?,?)', [$UID, $PostID]);
        // 或許可以不用return
        return response('{"liked": true}')
            ->header('content-type', 'application/json')
            ->header('charset', 'utf-8');
    }

    // 能夠取消當時登入的人他所按讚貼文的讚
    public function unlike(Request $request)
    {
        $UID = $request->UID;
        $PostID = $request->PostID;
        DB::delete('delete from LikeTable where UID=? AND PostID=?', [$UID, $PostID]);
        // 還是再次說明 或許不用return 或許可以拿來做測試
        return response('{"liked":false}')
            ->header('content-type', 'application/json')
            ->header('charset', 'utf-8');
    }


    // 能夠 能夠當使用者收藏的時候 傳給我們他所蒐藏的貼文ID(???)
// 以及我們必須自己去找當時登入的人是誰 他的ID(???)
    public function collect(Request $request)
    {
        $UID = $request->UID;
        $PostID = $request->PostID;
        DB::insert('insert into CollectTable (UID, PostID) values (?,?)', [$UID, $PostID]);
        // 或許可以不用return
        return response('{"collected": true}')
            ->header('content-type', 'application/json')
            ->header('charset', 'utf-8');
    }

    // 能夠取消當時登入的人他所蒐藏的蒐藏
    public function uncollect(Request $request)
    {
        $UID = $request->UID;
        $PostID = $request->PostID;
        DB::delete('delete from CollectTable where UID=? AND PostID=?', [$UID, $PostID]);
        // 還是再次說明 或許不用return 或許可以拿來做測試
        return response('{"collected":false}')
            ->header('content-type', 'application/json')
            ->header('charset', 'utf-8');
    }

    // 能夠取得 (__、依時間最晚發?)的五則貼文
    public function getmenpost(Request $request)
    {
        $UID = $request->UID;
        $fivePosts = DB::select('select Outfit.UID as AuthorID, Post.PostID, UserName, Avatar, EditedPhoto, FilterStyle, Userlike.UID as UserLike, Userkeep.UID as UserKeep from Post 
                                        left join Outfit on Outfit.OutfitID = Post.OutfitID
                                        left join Member on Outfit.UID = Member.UID
                                        left join (select * from LikeTable where UID = ?) as Userlike on Userlike.PostID = Post.PostID
                                        left join (select * from CollectTable where UID = ?) as Userkeep on Userkeep.PostID = Post.PostID
                                        where Member.Gender=1
                                        order by Post.PostID DESC
                                        limit 5;', [$UID, $UID]);

        return $fivePosts;
    }

    // 拿女人的時間最晚的五則po文
    public function getwomenpost(Request $request)
    {
        $UID = $request->UID;
        $fivePosts = DB::select('select Outfit.UID as AuthorID, Post.PostID, UserName, Avatar, EditedPhoto, FilterStyle, Userlike.UID as UserLike, Userkeep.UID as UserKeep from Post 
                                        left join Outfit on Outfit.OutfitID=Post.OutfitID
                                        left join Member on Outfit.UID=Member.UID
                                        left join (select * from LikeTable where UID = ?) as Userlike on Userlike.PostID = Post.PostID
                                        left join (select * from CollectTable where UID = ?) as Userkeep on Userkeep.PostID = Post.PostID
                                        where Member.Gender=0
                                        order by Post.PostID DESC
                                        limit 5;', [$UID, $UID]);
        return $fivePosts;
    }


    // 搜尋
    public function search(Request $request)
    {
        // 驗證數字有沒有超過
        $validated = $request->validate([
            'keyword' => 'required|string|max:20'
        ]);
        $keyword = '%' . htmlentities($validated['keyword'], ENT_QUOTES | ENT_HTML5) . '%';

        $result = DB::select("select EditedPhoto, Avatar, UserName from(
                                    select Post.PostID, Outfit.EditedPhoto, FilterStyle, Member.Avatar, Member.UserName from Post
                                    left join Outfit on Outfit.OutfitID = Post.OutfitID
                                    left join TagList on TagList.OutfitID = Outfit.OutfitID
                                    left join Item on Item.ItemID = TagList.ItemID
                                    left join Member on Member.UID = Outfit.UID
                                    where Item.Title like ? or Outfit.Title like ?) as result
                                    group by PostID;", [$keyword, $keyword]);
        return $result;
    }

    // 複雜搜尋
    public function complicatedsearch(Request $request)
    {
        $clothesType = $request->clothesType;
        $color = '%' . $request->color . '%'; 
        $brand = $request->brand;
        $size = $request->size;
        $season = $request->season;

        // 這個地方顏色另外建欄位??
        $result = DB::select("select EditedPhoto, Avatar, UserName from(
                                        select Post.PostID, Outfit.EditedPhoto, FilterStyle, Member.Avatar, Member.UserName from Post
                                        left join Outfit on Outfit.OutfitID = Post.OutfitID
                                        left join TagList on TagList.OutfitID = Outfit.OutfitID
                                        left join Item on Item.ItemID = TagList.ItemID
                                        left join Member on Member.UID = Outfit.UID
                                        where ( ? = 'default' or Item.Type = ? ) 
                                        AND (? = 'default' or Item.Brand = ? )
                                        AND (? = 'default' or Item.Size = ? ) 
                                        AND (? = 'default' or Outfit.Season = ? ) 
                                        AND Item.Title like ?) as result
                                        group by PostID;", [$clothesType, $clothesType, $brand, $brand, $size, $size, $season, $season, $color]);
        return $result;
    }

    // 純條件搜尋

    //衣服
    public function getClothesTypeID(Request $request)
    {
        $clothesType = $request->clothesType;
        $result = DB::select("SELECT TypeID FROM Type WHERE Name=?", [$clothesType]);
        return $result;
    }

    // 抓衣服品牌
    public function brand()
    {
        $fiveBrand = DB::select('Select Brand FROM Item
                                    group by Brand
                                    ORDER BY count(Brand) DESC
                                    limit 6;');
        return $fiveBrand;
    }

    // 抓衣服類別
    public function clothestype()
    {
        $sixclothes = DB::select('select Name from Item
                                left join Type on Type.TypeID = Item.Type
                                group by Name
                                order by count(Name) DESC
                                limit 6;');
        return $sixclothes;
    }


    // 使用者個人頁面
    // 抓使用者post
    public function getuserpost(Request $request)
    {
        $UID = $request->UID;
        $post = DB::select('select EditedPhoto, FilterStyle FROM Post
                                left join Outfit on Outfit.OutfitID = Post.OutfitID
                                where UID=?
                                order by PostID DESC;', [$UID]);
        return $post;
    }

    // 抓使用者collect
    public function getusercollect(Request $request)
    {
        $UID = $request->UID;
        $post = DB::select('select EditedPhoto, FilterStyle FROM CollectTable
                                left join Post on Post.PostID = CollectTable.PostID
                                left join Outfit on Outfit.OutfitID = Post.OutfitID
                                where CollectTable.UID=?
                                order by CollectTable.PostID DESC;', [$UID]);
        return $post;
    }


    // 抓貼文數
    public function getpostnum(Request $request)
    {
        $UID = $request->UID;
        $postNum = DB::select('select count(PostID) as postNum from Post
                                left join Outfit ON Outfit.OutfitID=Post.OutfitID
                                where UID=?;', [$UID]);
        return $postNum;
    }

    // userinfo 的所有api
    // 能夠抓取user 所有的資料 利用上面的api去做
    // 要放在selfpage裡面
    public function userself(Request $request)
    {
        $UID = $request->UID;
        $info = DB::select('select UserName, Avatar from Member
                                where UID=?;', [$UID]);
        return $info;
    }

    // 獲取粉絲數
    public function follownum(Request $request){
        $UID = $request->UID;
        $fanNumber = DB::select('select count(*) as FanNumber from FollowTable where FollowedUID=?  ',[$UID]);
        return $fanNumber;
    }


    // 放在其他人頁面的資訊
    public function otherppl(Request $request)
    {
        $UID = $request->UID;
        $info = DB::select('SELECT UserName, Avatar, UserIntro FROM Member WHERE UID = ?', [$UID]);
        return $info;
    }

    // 追蹤
    public function follow(Request $request){
        $authorID = $request->authorID;
        $UID = $request->UID;
        DB::insert('insert into FollowTable (FollowedUID, FollowerUID) VALUES (?,?)',[$authorID, $UID]);
    }

    // 退追蹤
    public function unfollow(Request $request){
        $authorID = $request->authorID;
        $UID = $request->UID;
        DB::delete('delete from FollowTable where FollowedUID = ? and FollowerUID = ?',[$authorID, $UID]);
    }

    // 有沒有誰追蹤誰
    public function followcheck(Request $request){
        $authorID = $request->authorID;
        $UID = $request->UID;
        $result = DB::select('select count(*) as FollowCheck from FollowTable where FollowedUID = ? and FollowerUID = ?',[$authorID, $UID]);
        return $result;
    }


    // 發文
    public function postPost(Request $request){
        $OutfitID = $request->OutfitID;
        DB::insert('insert into Post (OutfitID) values (?)',[$OutfitID]);
    }
}
