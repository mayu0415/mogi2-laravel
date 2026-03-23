<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ別勤怠一覧</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-staff-attendance-list.css') }}">
</head>
<body>
@include('header-admin')

<main class="staff-attendance-list">
    <h1 class="staff-attendance-list__title">{{ $staff->name }}さんの勤怠</h1>

    <div class="staff-attendance-list__month-nav">
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $prevMonth]) }}"
        class="staff-attendance-list__month-link">
            ← 前月
        </a>

        <div class="staff-attendance-list__month-current">
            {{ $displayMonth }}
        </div>

        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}"
        class="staff-attendance-list__month-link">
            翌月 →
        </a>
    </div>

    <div class="staff-attendance-list__table-wrapper">
        <table class="staff-attendance-list__table">
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
                        <td>{{ $day['date']->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$day['date']->dayOfWeek] }})</td>
                        <td>{{ $day['clock_in'] }}</td>
                        <td>{{ $day['clock_out'] }}</td>
                        <td>{{ $day['break_total'] }}</td>
                        <td>{{ $day['work_total'] }}</td>
                        <td>
                            @if($day['attendance'])
                                <a href="{{ route('admin.attendance.detail', ['id' => $day['attendance']->id]) }}"
                                class="staff-attendance-list__detail-link">詳細</a>
                            @else
                                <span class="staff-attendance-list__detail-empty">詳細</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="staff-attendance-list__csv-button-area">
        <a href="{{ route('admin.attendance.staff.csv', ['id' => $staff->id, 'month' => request('month') ?? \Carbon\Carbon::now()->format('Y-m')]) }}" class="staff-attendance-list__csv-button">
            CSV出力
        </a>
    </div>
</main>
</body>
</html>