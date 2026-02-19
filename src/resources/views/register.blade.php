<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録</title>

    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>
<body>

@include('header')

<main class="register">
    <h1 class="register__title">会員登録</h1>
    <form method="POST" action="{{ route('register') }}" class="register__form" novalidate>
        @csrf
        {{-- ユーザー名 --}}
        <div class="form-group">
            <label>ユーザー名</label>
            <input type="text" name="name" value="{{ old('name') }}">
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>
        {{-- メールアドレス --}}
        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}">
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>
        {{-- パスワード --}}
        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password">
            @if($errors->has('password'))
                @foreach($errors->get('password') as $msg)
                    @if($msg !== 'パスワードと一致しません')
                        <p class="error">{{ $msg }}</p>
                    @endif
                @endforeach
            @endif
        </div>
        {{-- 確認用パスワード --}}
        <div class="form-group">
            <label>確認用パスワード</label>
            <input type="password" name="password_confirmation">
            @if($errors->has('password'))
                @foreach($errors->get('password') as $msg)
                    @if($msg === 'パスワードと一致しません')
                        <p class="error">{{ $msg }}</p>
                    @endif
                @endforeach
            @endif
        </div>
        <button type="submit" class="register__button">
            登録する
        </button>
    </form>
    <div class="register__link">
        <a href="{{ route('login') }}">ログインはこちら</a>
    </div>
</main>

</body>
</html>