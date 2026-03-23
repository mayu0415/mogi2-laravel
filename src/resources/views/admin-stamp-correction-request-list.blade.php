<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請一覧</title>

    <link rel="stylesheet" href="{{ asset('css/header-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stamp-correction-request-list.css') }}">
</head>
<body>
@include('header-admin')

<main class="request-list">
    <h1 class="request-list__title">申請一覧</h1>

    <div class="request-list__tabs">
        <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'pending']) }}"
        class="request-list__tab {{ $status === 'pending' ? 'request-list__tab--active' : '' }}">承認待ち</a>
        <a href="{{ route('admin.stamp_correction_request.list', ['status' => 'approved']) }}"
        class="request-list__tab {{ $status === 'approved' ? 'request-list__tab--active' : '' }}">承認済み</a>
    </div>

    <div class="request-list__table-wrapper">
        <table class="request-list__table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $requestItem)
                    <tr>
                        <td>
                            {{ $requestItem->status === 'pending' ? '承認待ち' : '承認済み' }}
                        </td>
                        <td>
                            {{ $requestItem->attendance->user->name }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($requestItem->attendance->work_date)->format('Y/m/d') }}
                        </td>
                        <td>
                            {{ $requestItem->note }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($requestItem->created_at)->format('Y/m/d') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.stamp_correction_request.approve', ['id' => $requestItem->id]) }}"class="request-list__detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
</body>
</html>