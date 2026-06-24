<section>
    <h2 class="h5 mb-1">アカウント削除</h2>
    <p class="text-secondary small mb-4">
        アカウントを削除すると、すべてのデータが完全に削除されます。削除前に必要な情報をバックアップしてください。
    </p>

    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
        アカウントを削除
    </button>

    <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="confirmUserDeletionLabel">アカウントを削除しますか？</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-secondary small">
                            アカウントを削除すると、すべてのデータが完全に削除されます。続行するにはパスワードを入力してください。
                        </p>

                        <div class="mt-3">
                            <label for="password" class="form-label">パスワード</label>
                            <input id="password" name="password" type="password" class="form-control" placeholder="パスワード">
                            @error('password', 'userDeletion')
                                <ul class="text-danger small mb-0 mt-1"><li>{{ $message }}</li></ul>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-danger">削除する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if ($errors->userDeletion->isNotEmpty())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('confirmUserDeletion')).show();
        });
    </script>
    @endpush
@endif
