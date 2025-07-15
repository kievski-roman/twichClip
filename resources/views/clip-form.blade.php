@extends('layouts.app')
@section('content')
<form action="{{ route('clip.get') }}" method="POST">
    @csrf
    <label>Введи нік стрімера:</label>
    <input type="text" name="username" required>
    <button type="submit">Отримати кліпи</button>
</form>
@if ($errors->any())
    <p class="text-red-600 mt-4">{{ $errors->first() }}</p>
@endif
@endsection
