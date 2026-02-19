<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール設定</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile-edit.css') }}">
</head>
<body>

@include('header-auth')

<main class="profile">
    <h1 class="profile__title">プロフィール設定</h1>



    <form action="{{ route('profile.update') }}" method="POST" class="profile__form" enctype="multipart/form-data">
        @csrf
        
        <div class="profile__image">
            @if(!empty($user->profile_image))
                <img
                    src="{{ asset('storage/' . $user->profile_image) }}"
                    alt="プロフィール画像"
                    class="profile__circle"
                >
            @else
                <div class="profile__circle"></div>
            @endif

            <input
                type="file"
                name="profile_image"
                id="profile_image"
                class="profile__file"
                accept="image/*"
            >
            <label for="profile_image" class="profile__image-button">
                画像を選択する
            </label>
        </div>

        <label class="profile_label">ユーザー名</label>
        <input
            type="text"
            name="name"
            class="profile_input"
            value="{{ old('name', $user->name) }}"
        >

        <label class="profile_label">郵便番号</label>
        <input
            type="text"
            name="postal_code"
            class="profile_input"
            value="{{ old('postal_code', $user->postal_code) }}"
        >

        <label class="profile_label">住所</label>
        <input
            type="text"
            name="address"
            class="profile_input"
            value="{{ old('address', $user->address) }}"
        >

        <label class="profile_label">建物名</label>
        <input
            type="text"
            name="building_name"
            class="profile_input"
            value="{{ old('building_name', $user->building_name) }}"
        >

        <button class="profile__submit">更新する</button>
    </form>
</main>

</body>
</html>







