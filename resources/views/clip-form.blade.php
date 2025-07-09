<form action="{{ route('clip.get') }}" method="POST">
    @csrf
    <label>Введи нік стрімера:</label>
    <input type="text" name="username" required>
    <button type="submit">Отримати кліпи</button>
</form>
