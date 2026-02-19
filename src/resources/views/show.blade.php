<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品詳細</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/item.css') }}">
</head>
<body>
    @include('partials.shift')

    <main class="item-detail">

        <section class="item-detail__top">
            <div class="item-detail__image">
                @if($item->image_path)
    <img src="{{ asset($item->image_path) }}" alt="{{ $item->name }}">
                @else
                    <div class="item-detail__placeholder">商品画像</div>
                @endif
            </div>
            <div class="item-detail__info">
                <h1 class="item-detail__name">{{ $item->name }}</h1>

                @if($item->brand)
                    <p class="item-detail__brand">{{ $item->brand }}</p>
                @endif

                <p class="item-detail__price">
                    ¥{{ number_format($item->price) }} <span>(税込)</span>
                </p>

                <div class="item-detail__actions">
                    @auth
                    <form action="{{ route('item.favorite', $item) }}" method="POST">
                        @csrf
                            @php
                                $liked = $item->favorites->where('user_id', auth()->id())->count() > 0;
                            @endphp
                            <button type="submit" class="icon-button {{ $liked ? 'is-liked' : '' }}">
                                {{ $liked ? '💗' : '♡' }}
                            </button>
                    </form>
                    @endauth

                    @guest
                        <a href="{{ route('login') }}" class="icon-button">♡</a>
                    @endguest

                    <span class="count">{{ $item->favorites->count() }}</span>

                    <span class="icon-button">💬</span>
                    <span class="count">{{ $item->comments->count() }}</span>
                  </button>
                </div>

                <a href="{{ route('purchase', $item) }}" class="item-detail__buy">
                  購入手続きへ
                </a>
            </div>
        </section>

        <section class="item-detail__description">
            <h2>商品説明</h2>
            <p>{{ $item->description }}</p>
        </section>

            <section class="item-detail__data">
            <h2>商品の情報</h2>

                <p>
                カテゴリ：
                @forelse($item->categories as $category)
                    <span class="item-tag">{{ $category->name }}</span>
                @empty
                    <span class="item-tag item-tag--none">未分類</span>
                @endforelse
                </p>

            <p>
                商品の状態：{{ $item->condition }}
            </p>
        </section>


        <section class="item-detail__comments">
            <h2>コメント（{{ $item->comments->count() }}）</h2>

            @foreach($item->comments as $comment)
                <div class="comment">
                  <div class="comment__user">
                    <div class="comment__icon">
                        @if ($comment->user->profile_image)
                            <img src="{{ asset($comment->user->profile_image) }}" alt="icon">
                        @else
                            <div class="comment__icon--placeholder"></div>
                        @endif
                    </div>
                    <span class="comment__name">{{ $comment->user->name }}</span>
                  </div>
                  <p class="comment__content">{{ $comment->body }}</p>
                </div>
            @endforeach
        </section>

        @auth
        <section class="item-detail__comment-form">
            <h2>商品へのコメント</h2>

            {{-- エラーメッセージ --}}
            @if ($errors->has('body'))
                <p class="error-message">
                    {{ $errors->first('body') }}
                </p>
            @endif

            <form action="{{ route('item.favorite', $item) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="comment">

                <textarea name="body" required>{{ old('body') }}</textarea>

            <button type="submit" class="btn-red">
                コメントを送信する
            </button>
            </form>
        </section>
        @endauth

    </main>
</body>
</html>