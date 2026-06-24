<section>
    <h2 class="h5 mb-1">プロフィール情報</h2>
    <p class="text-secondary small mb-4">名前とメールアドレスを更新できます。</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">名前</label>
            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="form-label">メールアドレス</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small text-secondary mb-1">
                        メールアドレスが未確認です。
                        <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">確認メールを再送する</button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="small text-success mb-0">確認メールを送信しました。</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-accent">保存</button>

            @if (session('status') === 'profile-updated')
                <span class="small text-success">保存しました。</span>
            @endif
        </div>
    </form>
</section>
