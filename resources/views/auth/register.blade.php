@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">名前</label>
            <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required autocomplete="name">
            @error('name')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">メールアドレス</label>
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autocomplete="username">
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

        <div class="d-flex align-items-center justify-content-between">
            <a class="small text-secondary" href="{{ route('login') }}">すでに登録済みの方</a>
            <button type="submit" class="btn btn-accent">登録</button>
        </div>
    </form>
@endsection
