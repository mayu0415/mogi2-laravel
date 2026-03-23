<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
</head>
<body>
@include('header-auth')

<?php
    $workDate = \Carbon\Carbon::parse($attendance->work_date);
?>

<main class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>

    @if(session('success'))
        <p class="attendance-detail__success">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p class="attendance-detail__error">{{ session('error') }}</p>
    @endif

    @if($isPending)
        <p class="attendance-detail__error">承認待ちのため修正はできません。</p>
    @endif

    <form method="POST" action="{{ route('attendance.detail.update', ['id' => $attendance->id]) }}">
        @csrf

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
                    <input type="time" name="clock_in"
                        value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                        {{ $isPending ? 'disabled' : '' }}>
                    <span>〜</span>
                    <input type="time" name="clock_out"
                        value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                        {{ $isPending ? 'disabled' : '' }}>
                </div>
            </div>
            @error('clock_in')
                <p class="attendance-detail__validation">{{ $message }}</p>
            @enderror

            @foreach($breaks as $index => $break)
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </div>
                    <div class="attendance-detail__value attendance-detail__value--time">
                        <input type="time" name="breaks[{{ $index }}][break_start]"
                            value="{{ old("breaks.$index.break_start", $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}"
                            {{ $isPending ? 'disabled' : '' }}>
                        <span>〜</span>
                        <input type="time" name="breaks[{{ $index }}][break_end]"
                            value="{{ old("breaks.$index.break_end", $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}"
                            {{ $isPending ? 'disabled' : '' }}>
                    </div>
                </div>
            @endforeach

                @error('break_start')
                    <p class="attendance-detail__validation">{{ $message }}</p>
                @enderror

                @error('break_end')
                    <p class="attendance-detail__validation">{{ $message }}</p>
                @enderror

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">
                    {{ $breaks->count() === 0 ? '休憩' : '休憩' . ($breaks->count() + 1) }}
                </div>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <input type="time" name="breaks[{{ $breaks->count() }}][break_start]" value="{{ old("breaks." . $breaks->count() . ".break_start") }}" {{ $isPending ? 'disabled' : '' }}>
                    <span>〜</span>
                    <input type="time" name="breaks[{{ $breaks->count() }}][break_end]" value="{{ old("breaks." . $breaks->count() . ".break_end") }}" {{ $isPending ? 'disabled' : '' }}>
                </div>
            </div>

            <div class="attendance-detail__row">
                <div class="attendance-detail__label">備考</div>
                <div class="attendance-detail__value">
                    <textarea name="note" {{ $isPending ? 'disabled' : '' }}>{{ old('note') }}</textarea>
                </div>
            </div>
            @error('note')
                <p class="attendance-detail__validation">{{ $message }}</p>
            @enderror
        </div>

        @unless($isPending)
            <div class="attendance-detail__button-area">
                <button type="submit" class="attendance-detail__button">修正</button>
            </div>
        @endunless
    </form>
</main>
</body>
</html>