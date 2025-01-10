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
        DB::insert('insert into liketable (UID, PostID) values (?,?)', [$UID, $PostID]);
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
        DB::delete('delete from liketable where UID=? AND PostID=?', [$UID, $PostID]);
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
        DB::insert('insert into collecttable (UID, PostID) values (?,?)', [$UID, $PostID]);
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
        DB::delete('delete from collecttable where UID=? AND PostID=?', [$UID, $PostID]);
        // 還是再次說明 或許不用return 或許可以拿來做測試
        return response('{"collected":false}')
            ->header('content-type', 'application/json')
            ->header('charset', 'utf-8');
    }

    // 能夠取得 (__、依時間最晚發?)的五則貼文
    public function getmenpost(Request $request)
    {
        $UID = $request->UID;
        $fivePosts = DB::select('select outfit.UID as AuthorID, post.PostID, UserName, Avatar, EditedPhoto, FilterStyle, userlike.UID as UserLike, userkeep.UID as UserKeep from post 
                                        left join outfit on outfit.OutfitID=post.OutfitID
                                        left join member on outfit.UID=member.UID
                                        left join (select * from liketable where UID = ?) as userlike on userlike.PostID = post.PostID
                                        left join (select * from collecttable where UID = ?) as userkeep on userkeep.PostID = post.PostID
                                        where member.Gender=1
                                        order by post.PostID DESC
                                        limit 5;', [$UID, $UID]);

        return $fivePosts;
    }

    // 拿女人的時間最晚的五則po文
    public function getwomenpost(Request $request)
    {
        $UID = $request->UID;
        $fivePosts = DB::select('select outfit.UID as AuthorID, post.PostID, UserName, Avatar, EditedPhoto, FilterStyle, userlike.UID as UserLike, userkeep.UID as UserKeep from post 
                                        left join outfit on outfit.OutfitID=post.OutfitID
                                        left join member on outfit.UID=member.UID
                                        left join (select * from liketable where UID = ?) as userlike on userlike.PostID = post.PostID
                                        left join (select * from collecttable where UID = ?) as userkeep on userkeep.PostID = post.PostID
                                        where member.Gender=0
                                        order by post.PostID DESC
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
                                    select post.PostID,outfit.EditedPhoto, FilterStyle, member.Avatar, member.UserName from post
                                    left join outfit on outfit.OutfitID = post.OutfitID
                                    left join taglist on taglist.OutfitID = outfit.OutfitID
                                    left join item on item.ItemID = taglist.ItemID
                                    left join member on member.UID = outfit.UID
                                    where item.Title like ? or outfit.Title like ?) as result
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
                                        select post.PostID,outfit.EditedPhoto, FilterStyle, member.Avatar, member.UserName from post
                                        left join outfit on outfit.OutfitID = post.OutfitID
                                        left join taglist on taglist.OutfitID = outfit.OutfitID
                                        left join item on item.ItemID = taglist.ItemID
                                        left join member on member.UID = outfit.UID
                                        where ( ? = 'default' or item.Type = ? ) 
                                        AND (? = 'default' or item.Brand = ? )
                                        AND (? = 'default' or item.Size = ? ) 
                                        AND (? = 'default' or outfit.Season = ? ) 
                                        AND item.Title like ?) as result
                                        group by PostID;", [$clothesType, $clothesType, $brand, $brand, $size, $size, $season, $season, $color]);
        return $result;
    }

    // 純條件搜尋

    //衣服
    public function getClothesTypeID(Request $request)
    {
        $clothesType = $request->clothesType;
        $result = DB::select("SELECT TypeID FROM `type` WHERE Name=?", [$clothesType]);
        return $result;
    }

    // 抓衣服品牌
    public function brand()
    {
        $fiveBrand = DB::select('Select Brand FROM item
                                    group by Brand
                                    ORDER BY count(Brand) DESC
                                    limit 6;');
        return $fiveBrand;
    }

    // 抓衣服類別
    public function clothestype()
    {
        $sixclothes = DB::select('select Name from item
                                left join type on type.TypeID=item.Type
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
                                left join outfit on outfit.OutfitID = post.OutfitID
                                where UID=?
                                order by PostID DESC;', [$UID]);
        return $post;
    }

    // 抓使用者collect
    public function getusercollect(Request $request)
    {
        $UID = $request->UID;
        $post = DB::select('select EditedPhoto, FilterStyle FROM collecttable
                                left join post on post.PostID = collecttable.PostID
                                left join outfit on outfit.OutfitID =post.OutfitID
                                where collecttable.UID=?
                                order by collecttable.PostID DESC;', [$UID]);
        return $post;
    }


    // 抓貼文數
    public function getpostnum(Request $request)
    {
        $UID = $request->UID;
        $postNum = DB::select('select count(postID) as postNum from post
                                left join outfit ON outfit.outfitID=post.outfitID
                                where UID=?;', [$UID]);
        return $postNum;
    }

    // userinfo 的所有api
    // 能夠抓取user 所有的資料 利用上面的api去做
    // 要放在selfpage裡面
    public function userself(Request $request)
    {
        $UID = $request->UID;
        $info = DB::select('select UserName, Avatar from member
                                where UID=?;', [$UID]);
        return $info;
    }

    public function follownum(Request $request){
        $UID = $request->UID;
        $fanNumber = DB::select('select count(*) as FanNumber from followtable where FollowedUID=?  ',[$UID]);
        return $fanNumber;
    }


    // 放在其他人頁面的資訊
    public function otherppl(Request $request)
    {
        $UID = $request->UID;
        $info = DB::select('SELECT UserName, Avatar, UserIntro FROM member WHERE UID = ?', [$UID]);
        return $info;
    }

    public function follow(Request $request){
        $authorID = $request->authorID;
        $UID = $request->UID;
        DB::insert('insert into followtable (FollowedUID,FollowerUID) VALUES (?,?)',[$authorID, $UID]);
    }

    public function unfollow(Request $request){
        $authorID = $request->authorID;
        $UID = $request->UID;
        DB::delete('delete from followtable where FollowedUID = ? and FollowerUID = ?',[$authorID, $UID]);
    }

    public function followcheck(Request $request){
        $authorID = $request->authorID;
        $UID = $request->UID;
        $result = DB::select('select count(*) as FollowCheck from followtable where FollowedUID = ? and FollowerUID = ?',[$authorID, $UID]);
        return $result;
    }


    // 發文
    public function postPost(Request $request){
        $OutfitID = $request->OutfitID;
        DB::insert('insert into post (OutfitID) values (?)',[$OutfitID]);
    }
}
