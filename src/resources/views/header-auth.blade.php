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
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="header-auth__link">ログアウト</button>
        </form>
        <a href="{{ route('mypage') }}" class="header-auth__link">マイページ</a>
        <a href="{{ route('sell') }}" class="header-auth__button">出品</a>
    </nav>
</header>







