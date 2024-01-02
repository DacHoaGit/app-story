<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Story;
use App\Models\StoryFollow;
use App\Models\StoryType;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    //
    public function comment(Request $request)
    {
        try {
            $validateComment = Validator::make(
                $request->all(),
                [
                    'story_id' => 'required',
                    'content' => 'required',
                ]
            );

            if ($validateComment->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateComment->errors()
                ], 401);
            }
            $parent_id = $request->input('parent_id');
            $parent_comment = Comment::where('id', $parent_id)->get();

            if ($request->input('parent_id') && !$parent_comment) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => 'Stories Parent Not Exits'
                ], 401);
            }


            $data = $request->all();
            $data['user_id'] = Auth::user()->id;
            Comment::create($data);


            return response()->json([
                'status' => true,
                'message' => 'Comment Success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function storyDetail(Request $request, $id)
    {
        try {
            $data = Story::where('id', $id)->with([
                'chapters', 'storyType', 'comments.user', 'comments.childComments.user'
            ])->first();

            $follow = StoryFollow::where('story_id', $id)->where('user_id', Auth::user()->id)->first();
            if ($follow) {
                $data['is_follow'] = true;
            } else {
                $data['is_follow'] = false;
            }

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getStories(Request $request)
    {
        try {

            $data = StoryType::with('stories')->get();
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function theloaitruyen(Request $request)
    {

        try {

            $arr_truyen['listtruyen'] = array();

            if (!empty($request->input('theloai'))) {
                $truyen = DB::table('stories')
                    ->leftJoin('story_types', 'story_types.id', '=', 'stories.story_type_id')
                    ->select(
                        'stories.id',
                        'stories.name as tentruyen',
                        'stories.description as tomtatnd',
                        'stories.chap_number as chuongmoi',
                        'story_types.name as theloaitext',
                        'stories.score as danhgia',
                        'stories.image',
                        DB::raw('CASE 
                            WHEN stories.status = 0 THEN "Tạm dừng" 
                            WHEN stories.status = 1 THEN "Hoàn thành" 
                            ELSE "Đang cập nhật" END as trangthaitext'),
                        DB::raw('DATE_FORMAT(stories.created_at, "%d-%m-%Y") as ngaytao')
                    )
                    ->where('stories.story_type_id', $request->input('theloai'))
                    ->get();

                foreach ($truyen as $row) {
                    array_push($arr_truyen['listtruyen'], (object)[
                        'id' => $row->id,
                        'tentruyen' => $row->tentruyen,
                        'tomtatnd' => $row->tomtatnd,
                        'chuongmoi' => $row->chuongmoi,
                        'theloai' => $row->theloaitext,
                        'danhgia' => $row->danhgia,
                        'image' => $row->image,
                        'trangthai' => $row->trangthaitext,
                        'ngaytao' => $row->ngaytao,
                    ]);
                }
            }
            return response()->json($arr_truyen);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function thembinhluan(Request $request)
    {

        try {

            $request->validate([
                'id_truyen' => 'required',
                'noidung' => 'required',
            ]);

            $parent_id = $request->input('id_cha', null);
            $story_id = $request->input('id_truyen');
            $user_id = auth()->user()->id;
            $content = $request->input('noidung');

            DB::table('comments')->insert([
                'user_id' => $user_id,
                'story_id' => $story_id,
                'content' => $content,
                'parent_id' => $parent_id,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function timkiem(Request $request)
    {

        try {

            $request->validate([
                'text' => 'required',
            ]);

            $text = $request->input('text');

            $result = DB::table('stories')
                ->select(
                    'stories.id as idtruyen',
                    'stories.name as tentruyen',
                    'stories.description as tomtatnd',
                    'stories.chap_number as chuongmoi',
                    'story_types.name as theloai',
                    'stories.score as danhgia',
                    'image',
                    DB::raw('CASE 
                            WHEN stories.status = 0 THEN "Tạm dừng" 
                            WHEN stories.status = 1 THEN "Hoàn thành" 
                            ELSE "Đang cập nhật" END as trangthaitext'),
                    DB::raw('DATE_FORMAT(stories.created_at, "%d-%m-%Y") as ngaytao')
                )
                ->leftJoin('story_types', 'story_types.id', '=', 'stories.story_type_id')
                ->where('stories.name', 'LIKE', "%$text%")
                ->orWhere('story_types.name', 'LIKE', "%$text%")
                ->get();

            $arr_truyen['listtruyen'] = $result->toArray();

            return response()->json($arr_truyen);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function truyentheodoiuser(Request $request)
    {

        try {

            $result = DB::table('stories')
                ->select(
                    'stories.id as idtruyen',
                    'stories.name as tentruyen',
                    'stories.description as tomtatnd',
                    'stories.chap_number as chuongmoi',
                    'story_types.name as theloai',
                    'stories.score as danhgia',
                    'image',
                    DB::raw('CASE 
                            WHEN stories.status = 0 THEN "Tạm dừng" 
                            WHEN stories.status = 1 THEN "Hoàn thành" 
                            ELSE "Đang cập nhật" END as trangthaitext'),
                    DB::raw('DATE_FORMAT(stories.created_at, "%d-%m-%Y") as ngaytao')
                )
                ->leftJoin('story_follows', 'stories.id', '=', 'story_follows.story_id')
                ->leftJoin('users', 'users.id', '=', 'story_follows.user_id')
                ->leftJoin('story_types', 'story_types.id', '=', 'stories.id')
                ->where('story_follows.user_id', Auth::user()->id)
                ->get();
    
            $arr_truyen['listtruyen'] = $result->toArray();
    
            return response()->json($arr_truyen);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function noidungtruyen(Request $request)
    {

        try {

            $request->validate([
                'idchuong' => 'required',
            ]);
    
            $chapter_id = $request->input('idchuong');
    
            $result = DB::table('chapters')
                ->select('chapters.id', 'chapters.number as chuong', 'chapters.title as tieudechuong', 'chapters.content as noidung')
                ->leftJoin('stories', 'stories.id', '=', 'chapters.story_id')
                ->where('chapters.id', $chapter_id)
                ->first();
    
            $arr_chuongtruyen['id'] = $result->id;
            $arr_chuongtruyen['chuong'] = $result->chuong;
            $arr_chuongtruyen['tieudechuong'] = $result->tieudechuong;
            $arr_chuongtruyen['noidung'] = $result->noidung;
    
            return response()->json($arr_chuongtruyen);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function danhsachchuong(Request $request)
    {

        try {

            $request->validate([
                'idtruyen' => 'required',
            ]);
    
            $story_id = $request->input('idtruyen');
    
            $chuongtruyen = DB::table('chapters')
                ->select('chapters.id', 'chapters.number as chuong', 'chapters.title as tieudechuong')
                ->leftJoin('stories', 'stories.id', '=', 'chapters.story_id')
                ->where('chapters.story_id', $story_id)
                ->get();
    
            $arr_chuongtruyen['danhsach'] = $chuongtruyen->toArray();
    
            return response()->json($arr_chuongtruyen);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function checktruyentheodoi(Request $request)
    {

        try {

            $request->validate([
                'id_truyen' => 'required',
                'tt' => 'required',
            ]);
    
            $user_id = Auth::user()->id;
            $story_id = $request->input('id_truyen');
            $tt = $request->input('tt');
    
            $user = DB::table('story_follows')
                ->where('user_id', $user_id)
                ->where('story_id', $story_id)
                ->count();
    
            $response = [];
    
            if ($tt == 1) {
                $response['success'] = $user > 0;
            } elseif ($tt == 2) {
                if ($user > 0) {
                    DB::table('story_follows')
                        ->where('user_id', $user_id)
                        ->where('story_id', $story_id)
                        ->delete();
    
                    $response['success'] = 'Đã xóa khỏi danh sách yêu thích';
                } else {
                    DB::table('story_follows')->insert([
                        'user_id' => $user_id,
                        'story_id' => $story_id,
                    ]);
    
                    $response['success'] = 'Đã thêm vào danh sách yêu thích';
                }
            }
    
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function binhluan(Request $request)
    {

        try {

            $request->validate([
                'idtruyen' => 'required',
            ]);
    
            $story_id = $request->input('idtruyen');
    
            $arrBinhLuan = [];
            $truyenQuery = DB::table('comments')
                ->leftJoin('users', 'users.id', '=', 'comments.user_id')
                ->select('comments.id as idtr', 'users.name as user_id', 'comments.content as noidung')
                ->where('comments.story_id', $story_id)
                ->whereNull('comments.parent_id')
                ->get();
    
            foreach ($truyenQuery as $row) {
                $arrBinhLuan1 = [];
                $truyenQuery1 = DB::table('comments')
                    ->leftJoin('users', 'users.id', '=', 'comments.user_id')
                    ->select('comments.id as idtr', 'users.name as user_id', 'comments.content as noidung')
                    ->where('comments.story_id', $story_id)
                    ->where('comments.parent_id', $row->idtr)
                    ->get();
    
                foreach ($truyenQuery1 as $row1) {
                    array_push($arrBinhLuan1, (object)[
                        'idtr' => $row1->idtr,
                        'user_id' => $row1->user_id,
                        'truyen_id' => $story_id,
                        'noidung' => $row1->noidung,
                        'traloibinhluan' => '',
                    ]);
                }
    
                array_push($arrBinhLuan, (object)[
                    'idtr' => $row->idtr,
                    'user_id' => $row->user_id,
                    'truyen_id' => $story_id,
                    'noidung' => $row->noidung,
                    'traloibinhluan' => $arrBinhLuan1,
                ]);
            }
    
            return response()->json(['listtruyen' => $arrBinhLuan]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function slider(Request $request)
    {

        try {

          
            $story_type_id = $request->input('theloai') ?? 3;
    
            $arrTruyen = [];
            $truyenQuery = DB::table('stories')
                ->select('id', 'stories.name as tentruyen', 'image')
                ->where('stories.story_type_id', $story_type_id)
                ->get();
    
            foreach ($truyenQuery as $row) {
                array_push($arrTruyen, (object)[
                    'id' => $row->id,
                    'tentruyen' => $row->tentruyen,
                    'image' => $row->image,
                ]);
            }
    
            return response()->json(['danhsach' => $arrTruyen]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }



    


    
    
    

    public function getFollowStories(Request $request)
    {
        try {
            return response()->json([
                'status' => true,
                'data' => StoryFollow::with('story')->where('user_id', Auth::user()->id)->get()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
