<header class="header-auth">
        <div class="header-auth__left">
                <div class="header-auth__logo">COACHTECH</div>
        </div>

        <nav class="header-auth__nav">
                <a href="{{ route('admin.attendance.list') }}" class="header-auth__link">勤怠一覧</a>
                <a href="{{ route('admin.staff.list') }}" class="header-auth__link">スタッフ一覧</a>
                <a href="{{ route('admin.stamp_correction_request.list') }}" class="header-auth__link">申請一覧</a>

                <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <input type="hidden" name="logout_type" value="admin">
                        <button type="submit" class="header-auth__link">ログアウト</button>
                </form>
        </nav>
</header>