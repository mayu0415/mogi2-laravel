<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品の出品</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
</head>
<body>

@include('header-auth')

<main class="sell">
    <div class="sell__inner">

        <h1 class="sell__title">商品の出品</h1>

        <form
            action="{{ route('sell.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="sell-form"
        >
            @csrf

            {{-- 商品画像 --}}
            <section class="sell-block">
                <h2 class="sell-block__title">商品画像</h2>

                <div class="image-box">
                    <label class="image-box__label">
                        <span class="image-box__btn">画像を選択する</span>
                        <input type="file" name="image" accept="image/*">
                    </label>
                </div>

                @error('image')
                    <p class="sell-error">{{ $message }}</p>
                @enderror
            </section>

            {{-- 商品の詳細 --}}
            <section class="sell-block">
                <h2 class="sell-block__title sell-block__title--line">
                    商品の詳細
                </h2>

                {{-- カテゴリー --}}
                <div class="sell-field">
                    <p class="sell-field__label">カテゴリー</p>

                    <div class="category">
                        @foreach ($categories as $category)
                            <label class="category-pill">
                                <input
                                    type="checkbox"
                                    name="categories[]"
                                    value="{{ $category }}"
                                    {{ is_array(old('categories')) && in_array($category, old('categories')) ? 'checked' : '' }}
                                >
                                <span class="category-pill__text">{{ $category }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('categories')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 商品の状態 --}}
                <div class="sell-field">
                    <label class="sell-field__label">商品の状態</label>

                    <div class="select-wrap">
                        <select name="condition" class="sell-select">
                            <option value="">選択してください</option>
                            <option value="良好">良好</option>
                            <option value="目立った傷や汚れなし">目立った傷や汚れなし</option>
                            <option value="やや傷や汚れあり">やや傷や汚れあり</option>
                            <option value="状態が悪い">状態が悪い</option>
                        </select>
                    </div>

                    @error('condition')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- 商品名と説明 --}}
            <section class="sell-block">
                <h2 class="sell-block__title sell-block__title--line">
                    商品名と説明
                </h2>

                <div class="sell-field">
                    <label class="sell-field__label">商品名</label>
                    <input type="text" name="name" class="sell-input" value="{{ old('name') }}">
                    @error('name')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sell-field">
                    <label class="sell-field__label">ブランド名</label>
                    <input type="text" name="brand" class="sell-input" value="{{ old('brand') }}">
                </div>

                <div class="sell-field">
                    <label class="sell-field__label">商品の説明</label>
                    <textarea name="description" class="sell-textarea">{{ old('description') }}</textarea>
                </div>

                <div class="sell-field">
                    <label class="sell-field__label">販売価格</label>
                    <div class="price-wrap">
                        <span class="price-wrap__yen">¥</span>
                        <input type="number" name="price" class="sell-input sell-input--price" value="{{ old('price') }}">
                    </div>
                    @error('price')
                        <p class="sell-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <button type="submit" class="sell-submit">
                出品する
            </button>

        </form>
    </div>
</main>

</body>
</html>