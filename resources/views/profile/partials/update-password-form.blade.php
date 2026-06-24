<section>
    <h2 class="h5 mb-1">パスワード変更</h2>
    <p class="text-secondary small mb-4">安全のため、十分に長いランダムなパスワードを使用してください。</p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">現在のパスワード</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">新しいパスワード</label>
            <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password">
            @error('password', 'updatePassword')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label">新しいパスワード（確認）</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-accent">保存</button>

            @if (session('status') === 'password-updated')
                <span class="small text-success">保存しました。</span>
            @endif
        </div>
    </form>
</section>
