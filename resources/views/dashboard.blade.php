@extends('layouts.app')
    <x-user-dropdown />
@section('content')
    <x-panel-component />
@endsection
@php
    $hideNavigation = true;
@endphp
