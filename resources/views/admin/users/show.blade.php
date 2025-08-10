@extends('layouts.app')

@section('content')
    <livewire:admin.users.show-user :user="$user" />
@endsection