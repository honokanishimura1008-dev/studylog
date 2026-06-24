@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">メールアドレス</label>
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" required autocomplete="username">
            @error('email')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">パスワード</label>
            <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">
            @error('password')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">パスワード（確認）</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            @error('password_confirmation')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-accent">パスワードをリセット</button>
        </div>
    </form>
@endsection
