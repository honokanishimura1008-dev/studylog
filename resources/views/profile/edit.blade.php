@extends('layouts.app')

@section('content')
<div class="container py-4 py-lg-5">
    <h1 class="h3 mb-4">プロフィール</h1>

    <div class="row justify-content-center">
        <div class="col-lg-8 d-flex flex-column gap-4">
            <div class="panel p-4 p-md-5">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="panel p-4 p-md-5">
                @include('profile.partials.update-password-form')
            </div>

            <div class="panel p-4 p-md-5">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
