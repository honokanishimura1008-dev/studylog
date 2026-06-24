@extends('layouts.guest')

@section('content')
    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">メールアドレス</label>
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">パスワード</label>
            <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
            @error('password')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label small">ログイン状態を保持</label>
        </div>

        <div class="d-flex align-items-center justify-content-between">
            @if (Route::has('password.request'))
                <a class="small text-secondary" href="{{ route('password.request') }}">パスワードを忘れた場合</a>
            @endif

            <button type="submit" class="btn btn-accent">ログイン</button>
        </div>
    </form>
@endsection
