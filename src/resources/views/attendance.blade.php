<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠登録</title>
    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
</head>

<body>
@include('header-auth')

<?php
    $now = \Carbon\Carbon::now();
    $weekdays = ['日','月','火','水','木','金','土'];
    $isWorking = isset($attendance) && $attendance && $attendance->clock_in && !$attendance->clock_out;
    $isDone = isset($attendance) && $attendance && $attendance->clock_out;
    $isOnBreak = isset($onBreak) && $onBreak !== null;
?>
<main class="attendance">
    <div class="attendance__status">
        <?php if ($isDone): ?>
            退勤済
        <?php elseif ($isOnBreak): ?>
            休憩中
        <?php elseif ($isWorking): ?>
            出勤中
        <?php else: ?>
            勤務外
        <?php endif; ?>
    </div>
    <div class="attendance__date">
        {{ $now->format('Y年n月j日') }}
        ({{ $weekdays[$now->dayOfWeek] }})
    </div>
    <div class="attendance__time">
        {{ $now->format('H:i') }}
    </div>
    <div class="attendance__actions">
        <?php if ($isDone): ?>
            <p class="attendance__message">
                お疲れ様でした。
            </p>
        <?php elseif ($isWorking): ?>
        <?php if ($isOnBreak): ?>
            <form method="POST" action="{{ route('attendance.breakOut') }}">
                @csrf
                <button type="submit" class="attendance__button attendance__button--ghost">
                    休憩戻
                </button>
            </form>
        <?php else: ?>
            <form method="POST" action="{{ route('attendance.clockOut') }}">
                @csrf
                <button type="submit" class="attendance__button attendance__button--primary">
                    退勤
                </button>
            </form>
            <form method="POST" action="{{ route('attendance.breakIn') }}">
                @csrf
                <button type="submit" class="attendance__button attendance__button--ghost">
                    休憩入
                </button>
            </form>
        <?php endif; ?>
        <?php else: ?>
            <form method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button type="submit" class="attendance__button attendance__button--primary">
                    出勤
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>