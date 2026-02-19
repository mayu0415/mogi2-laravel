<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Comment;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Item::latest()->get();
        return view('index', compact('items'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = [
            'ファッション',
            '家電',
            'インテリア',
            'レディース',
            'メンズ',
            'コスメ',
            '本',
            'ゲーム',
            'スポーツ',
            'キッチン',
            'ハンドメイド',
            'アクセサリー',
            'おもちゃ',
            'ベビー・キッズ'
        ];

        $conditions = [
            '良好',
            '目立った傷や汚れなし',
            'やや傷や汚れあり',
            '傷や汚れあり',
            '状態が悪い'
        ];

        return view('sell', compact('categories', 'conditions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        $item->load([
            'user',
            'categories',
            'comments.user',
            'favorites',
        ]);
        return view('show', compact('item'));
    }

    public function favorite(Request $request, Item $item)
    {
        // 未ログインはログインへ
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($request->type === 'comment') {
            Comment::create([
                'user_id' => Auth::id(),
                'item_id' => $item->id,
                'body' => $request->body,
            ]);

            return redirect()->route('item', $item);
        }

        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first();
        if ($favorite) {
            // いいね解除
            $favorite->delete();
        } else {
            // いいね追加
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);
        }
        return redirect()->route('item', $item);
    }

    
}
