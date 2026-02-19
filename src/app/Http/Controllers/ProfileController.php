<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('page', 'sell');

        //出品した商品
        $sellItems = Item::where('user_id', $user->id)->get();
        //購入した商品（ordersテーブルがある前提）
        $buyItems = Item::whereHas('orders', function ($query) use ($user) {
        $query->where('user_id', $user->id);
        })->get();
        return view('profile', compact(
            'user',
            'tab',
            'sellItems',
            'buyItems'
        ));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile-edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $user->name = $request->input('name');
        $user->postal_code = $request->input('postal_code');
        $user->address = $request->input('address');
        $user->building_name = $request->input('building_name');

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store(
                'profile_images',
                'public'
                );

            $user->profile_image = $path;
        }

        $user->save();

        return redirect()->route('mypage');
    }
}