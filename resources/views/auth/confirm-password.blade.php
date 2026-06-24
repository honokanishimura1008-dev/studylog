@extends('layouts.guest')

@section('content')
    <p class="text-secondary small mb-4">
        安全な領域です。続行する前にパスワードを確認してください。
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label">パスワード</label>
            <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
            @error('password')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-accent">確認</button>
        </div>
    </form>
@endsection
