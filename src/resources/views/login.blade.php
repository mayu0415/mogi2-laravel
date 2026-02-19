<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>

    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

@include('header')

<main class="login">
    <h1 class="login__title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label>メールアドレス</label>
        <input type="email" name="email" value="{{ old('email') }}">
        @error('email')
            <p class="login__error">{{ $message }}</p>
        @enderror

        <label>パスワード</label>
        <input type="password" name="password">
        @error('password')
            <p class="login__error">{{ $message }}</p>
        @enderror

        <button class="login__submit">ログインする</button>
    </form>

    <div class="login__link">
        <a href="{{ route('register') }}">会員登録はこちら</a>
    </div>
</main>

</body>
</html>