<header class="header-auth">
    <div class="header-auth__left">
        <div class="header-auth__logo">COACHTECH</div>
    </div>

    <nav class="header-auth__nav">
        <a href="{{ route('attendance') }}" class="header-auth__link">勤怠</a>
        <a href="{{ route('attendance.list') }}" class="header-auth__link">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}" class="header-auth__link">申請</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="header-auth__link">ログアウト</button>
        </form>
    </nav>
</header>







