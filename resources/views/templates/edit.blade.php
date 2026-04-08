@extends('layouts.app')
@section('title', 'Sửa Template')
@section('page-title', 'Sửa Template: ' . $template->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">Sửa</li>
@endsection

@section('content')
    @include('templates.create')
@endsection
