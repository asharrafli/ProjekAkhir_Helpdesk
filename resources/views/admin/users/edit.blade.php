@extends('layouts.app')

@section('content')
    <livewire:admin.users.user-form :user="$user" />
@endsection