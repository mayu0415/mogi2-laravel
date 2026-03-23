<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>

    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
</head>
<body>
@include('header')

<main class="admin-login">
    <h1 class="admin-login__title">管理者ログイン</h1>

    <form method="POST" action="{{ route('login') }}" class="admin-login__form" novalidate>
        @csrf
        <input type="hidden" name="login_type" value="admin">

        <div class="admin-login__group">
            <label class="admin-login__label">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="admin-login__input">
            @error('email')
                <p class="admin-login__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="admin-login__group">
            <label class="admin-login__label">パスワード</label>
            <input type="password" name="password" class="admin-login__input">
            @error('password')
                <p class="admin-login__error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="admin-login__button">管理者ログインする</button>
    </form>
</main>
</body>
</html>