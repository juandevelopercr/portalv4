<div>
  @if (session('message'))
  <div class="alert alert-success">
    {{ session('message') }}
  </div>
  @endif

  <form wire:submit.prevent="sendEmail">
    <div>
      <label for="email">Correo Electrónico:</label>
      <input type="email" id="email" wire:model="email" required>
    </div>

    <div>
      <label for="subject">Asunto:</label>
      <input type="text" id="subject" wire:model="subject" required>
    </div>

    <div>
      <label for="message">Mensaje:</label>
      <textarea id="message" wire:model="message" required></textarea>
    </div>

    <div>
      <label>Adjuntar Archivos:</label>
      @foreach ($files as $file)
      <div>
        <input type="checkbox" wire:model="attachments" value="{{ $file }}" id="file_{{ $loop->index }}">
        <label for="file_{{ $loop->index }}">{{ $file }}</label>
      </div>
      @endforeach
    </div>

    <button type="submit">Enviar Correo</button>
  </form>
</div>
