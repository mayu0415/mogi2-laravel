<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品一覧</title>
    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body>
    @include('partials.shift')
    <main class="index">
        <div class="index__tabs">
            {{-- おすすめ: / --}}
            <a href="{{ route('index') }}"
               class="index__tab {{ request('tab') !== 'mylist' ? 'is-active' : '' }}">
                おすすめ
            </a>
            {{-- マイリスト: /?tab=mylist --}}
            <a href="{{ route('index', ['tab' => 'mylist']) }}"
               class="index__tab {{ request('tab') === 'mylist' ? 'is-active' : '' }}">
                マイリスト
            </a>
        </div>
        {{-- 商品一覧 --}}
        <section class="index__grid">
            @forelse ($items ?? [] as $item)
                <a href="{{ route('item', $item->id) }}" class="item-card">
                    <div class="item-card__image">
                        @if($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                        @else
                            <span class="item-card__placeholder">商品画像</span>
                        @endif
                    </div>
                    <p class="item-card__name">{{ $item->name }}</p>
                </a>
            @empty
                {{-- ダミー表示 --}}
                @for ($i = 0; $i < 3; $i++)
                    <div class="item-card">
                        <div class="item-card__image">
                            <span class="item-card__placeholder">商品画像</span>
                        </div>
                        <p class="item-card__name">商品名</p>
                    </div>
                @endfor
            @endforelse
        </section>
    </main>
</body>
</html>