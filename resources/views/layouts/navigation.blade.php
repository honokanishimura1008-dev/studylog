<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">MuscleLog</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">ダッシュボード</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('mussle-log.index') }}">履歴</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('weight-matrix') }}">比較表</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('materials.index') }}">種目</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('foods.index') }}">食事</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('menu') }}">メニュー</a>
                </li>
            </ul>

            <div class="dropdown ms-lg-4">
                <a class="text-secondary small text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    {{ Auth::user()->name }} ▾
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">プロフィール</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
