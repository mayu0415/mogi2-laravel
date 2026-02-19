<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
<body>
    
    @include('header-auth')
    <main class="profile">
        <section class="profile__top">
            <div class="profile__user">
                <div class="profile__avatar">
                    @if(!empty($user->profile_image))
                        <img
                            src="{{ asset('storage/' . $user->profile_image) }}"
                            alt="ユーザーアイコン"
                            class="profile__avatar-img"
                        >
                    @else
                        <div class="profile__avatar-placeholder"></div>
                    @endif
                </div>
                <div class="profile__name">
                    {{ $user->name ?? 'ユーザー名' }}
                </div>
            </div>
            <div class="profile__edit">
                <a href="{{ route('profile.edit') }}" class="profile__edit-button">
                    プロフィールを編集
                </a>
            </div>
        </section>
        
        <section class="profile__tabs">
            <a
                href="{{ route('mypage', ['page' => 'sell']) }}"
                class="profile__tab {{ ($tab ?? 'sell') === 'sell' ? 'is-active' : '' }}"
            >
                出品した商品
            </a>
            <a
                href="{{ route('mypage', ['page' => 'buy']) }}"
                class="profile__tab {{ ($tab ?? 'sell') === 'buy' ? 'is-active' : '' }}"
            >
                購入した商品
            </a>
        </section>
        {{-- 商品一覧 --}}
        <section class="profile__content">
            {{-- 出品した商品 --}}
            @if ($tab === 'sell')
                @forelse ($sellItems as $item)
                    <a href="{{ route('item', $item->id) }}" class="item-card">
                        <div class="item-card__image">
                            @if ($item->image_path)
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                            @else
                                <span class="item-card__placeholder">商品画像</span>
                            @endif
                            {{-- 売り切れ表示 --}}
                            @if ($item->is_sold)
                                <span class="item-card__sold">SOLD</span>
                            @endif
                        </div>
                        <p class="item-card__name">
                            {{ $item->name }}
                        </p>
                    </a>
                @empty
                    <p>出品した商品はありません</p>
                @endforelse
            @endif
            {{-- 購入した商品 --}}
            @if ($tab === 'buy')
                @forelse ($buyItems as $item)
                    <a href="{{ route('item', $item->id) }}" class="item-card">
                        <div class="item-card__image">
                            @if ($item->image_path)
                                <img src="{{ asset($item->image_path) }}" alt="{{ $item->name }}">
                            @else
                                <span class="item-card__placeholder">商品画像</span>
                            @endif
                            {{-- 購入済みは必ずSOLD --}}
                            <span class="item-card__sold">SOLD</span>
                        </div>
                        <p class="item-card__name">
                            {{ $item->name }}
                        </p>
                    </a>
                @empty
                    <p>購入した商品はありません</p>
                @endforelse
            @endif
        </section>
    </main>
</body>
</html>










