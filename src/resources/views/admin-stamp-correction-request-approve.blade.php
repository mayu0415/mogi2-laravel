<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-stamp-correction-request-approve.css') }}">
</head>
<body>
@include('header-admin')

<?php
$workDate = \Carbon\Carbon::parse($attendance->work_date);
?>

<main class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>

    @if(session('success'))
        <p class="attendance-detail__success">{{ session('success') }}</p>
    @endif

    <div class="attendance-detail__table">
        <div class="attendance-detail__row">
            <div class="attendance-detail__label">名前</div>
            <div class="attendance-detail__value attendance-detail__value--name">
                {{ $attendance->user->name }}
            </div>
        </div>

        <div class="attendance-detail__row">
            <div class="attendance-detail__label">日付</div>
            <div class="attendance-detail__value attendance-detail__value--date">
                <span>{{ $workDate->format('Y年') }}</span>
                <span>{{ $workDate->format('n月j日') }}</span>
            </div>
        </div>

        <div class="attendance-detail__row">
            <div class="attendance-detail__label">出勤・退勤</div>
            <div class="attendance-detail__value attendance-detail__value--time">
                <span class="attendance-detail__text">
                    {{ \Carbon\Carbon::parse($attendanceRequest->requested_clock_in)->format('H:i') }}
                </span>
                <span>〜</span>
                <span class="attendance-detail__text">
                    {{ \Carbon\Carbon::parse($attendanceRequest->requested_clock_out)->format('H:i') }}
                </span>
            </div>
        </div>

        @foreach($requestedBreaks as $index => $break)
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </div>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <span class="attendance-detail__text">
                        {{ !empty($break['break_start']) ? \Carbon\Carbon::parse($break['break_start'])->format('H:i') : '' }}
                    </span>
                    <span>〜</span>
                    <span class="attendance-detail__text">
                        {{ !empty($break['break_end']) ? \Carbon\Carbon::parse($break['break_end'])->format('H:i') : '' }}
                    </span>
                </div>
            </div>
        @endforeach

        <div class="attendance-detail__row">
            <div class="attendance-detail__label">
                {{ count($requestedBreaks) === 0 ? '休憩' : '休憩' . (count($requestedBreaks) + 1) }}
            </div>
            <div class="attendance-detail__value attendance-detail__value--time">
                <span class="attendance-detail__text"></span>
                <span>〜</span>
                <span class="attendance-detail__text"></span>
            </div>
        </div>

        <div class="attendance-detail__row">
            <div class="attendance-detail__label">備考</div>
            <div class="attendance-detail__value">
                <div class="attendance-detail__note-text">{{ $attendanceRequest->note }}</div>
            </div>
        </div>
    </div>

    <div class="attendance-detail__button-area">
        @if($isApproved)
            <button type="button" class="attendance-detail__button attendance-detail__button--approved" disabled>
                承認済み
            </button>
        @else
            <form method="POST" action="{{ route('admin.stamp_correction_request.approve.update', ['id' => $attendanceRequest->id]) }}">
                @csrf
                <button type="submit" class="attendance-detail__button">承認</button>
            </form>
        @endif
    </div>
</main>
</body>
</html>