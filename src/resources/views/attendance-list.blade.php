<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠一覧</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
</head>
<body>
@include('header-auth')

<main class="attendance-list">
    <h1 class="attendance-list__title">勤怠一覧</h1>

    <div class="attendance-list__month-nav">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="attendance-list__month-link">
            ← 前月
        </a>

        <div class="attendance-list__month-current">
            {{ $displayMonth }}
        </div>

        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="attendance-list__month-link">
            翌月 →
        </a>
    </div>

    <div class="attendance-list__table-wrapper">
        <table class="attendance-list__table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                    <tr>
                        <td>{{ $day['date_label'] }}</td>
                        <td>{{ $day['clock_in'] }}</td>
                        <td>{{ $day['clock_out'] }}</td>
                        <td>{{ $day['break_total'] }}</td>
                        <td>{{ $day['work_total'] }}</td>
                        <td>
                            @if($day['attendance'])
                                <a href="{{ route('attendance.detail', ['id' => $day['attendance']->id]) }}" class="attendance-list__detail-link">詳細</a>
                            @else
                                <span class="attendance-list__detail-empty">詳細</span>
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