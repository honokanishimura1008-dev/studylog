@extends('layouts.guest')

@section('content')
    <p class="text-secondary small mb-4">
        パスワードをお忘れの場合は、メールアドレスを入力してください。リセット用のリンクをお送りします。
    </p>

    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">メールアドレス</label>
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-accent">リセットリンクを送信</button>
        </div>
    </form>
@endsection
