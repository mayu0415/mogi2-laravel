<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者勤怠一覧</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
</head>
<body>
@include('header-admin')

<main class="admin-attendance-list">
    <h1 class="admin-attendance-list__title">{{ $displayDate }}の勤怠</h1>

    <div class="admin-attendance-list__date-nav">
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="admin-attendance-list__date-link">
            ← 前日
        </a>

        <div class="admin-attendance-list__date-current">
            {{ $navDate }}
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="admin-attendance-list__date-link">
            翌日 →
        </a>
    </div>

    <div class="admin-attendance-list__table-wrapper">
        <table class="admin-attendance-list__table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffAttendances as $item)
                    <tr>
                        <td>{{ $item['user']->name }}</td>
                        <td>{{ $item['clock_in'] }}</td>
                        <td>{{ $item['clock_out'] }}</td>
                        <td>{{ $item['break_total'] }}</td>
                        <td>{{ $item['work_total'] }}</td>
                        <td>
                            @if($item['attendance'])
                                <a href="{{ route('admin.attendance.detail', ['id' => $item['attendance']->id]) }}" class="admin-attendance-list__detail-link">
                                    詳細
                                </a>
                            @else
                                &nbsp;
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
</body>
</html>