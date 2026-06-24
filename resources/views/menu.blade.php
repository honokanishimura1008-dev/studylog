@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/menu.css') }}" rel="stylesheet">
@endpush

@section('content')
<div
  id="menu-app"
  data-props='@json($menuPageProps)'
></div>
@endsection

@push('scripts')
@vite(['resources/js/menu/main.ts'])
@endpush
