<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
</head>
<body>

    @include('header')

    <main class="verify-main">
        <div class="verify-container">
            <p class="verify-text">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>
            {{-- 認証メール送信 --}}
            <a href="http://localhost:8025/" target="_blank" class="verify-button">認証はこちらから</a>
            {{-- 再送リンク --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resend-button">認証メールを再送する</button>
            </form>
        </div>
    </main>
</body>
</html>









