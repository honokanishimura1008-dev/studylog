@extends('layouts.guest')

@section('content')
    <p class="text-secondary small mb-4">
        ご登録ありがとうございます。開始前に、お送りしたメールのリンクからメールアドレスを確認してください。メールが届かない場合は、再送できます。
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">確認メールを再送しました。</div>
    @endif

    <div class="d-flex align-items-center justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-accent">確認メールを再送</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-secondary">ログアウト</button>
        </form>
    </div>
@endsection
