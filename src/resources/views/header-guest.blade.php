<header class="header-auth">
    <div class="header-auth__left">
        <div class="header-auth__logo">COACHTECH</div>
        <input
            type="text"
            class="header-auth__search"
            placeholder="なにをお探しですか？"
        >
    </div>

    <nav class="header-auth__nav">
        <a href="{{ route('login') }}" class="header-auth__link">ログイン</a>
        <a href="{{ route('mypage') }}" class="header-auth__link">マイページ</a>
        <a href="{{ route('sell') }}" class="header-auth__button">出品</a>
    </nav>
</header>