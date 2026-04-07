<?php

namespace App\Filament\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketChat extends Component
{
    use WithFileUploads;

    public Ticket $record;

    public string $message = '';

    /**
     * @var array<int, \Illuminate\Http\UploadedFile>
     */
    public array $attachments = [];

    public function mount(Ticket $record): void
    {
        abort_unless(auth()->user()->can('view', $record), 403);

        $this->record = $record;
    }

    public function sendMessage(TicketService $ticketService): void
    {
        abort_unless($this->canSendMessages(), 403);

        $validated = $this->validate($this->rules(), $this->messages());

        $ticketService->addMessageToTicket($this->record->fresh(), auth()->user(), [
            'contenido' => $validated['message'],
            'attachments' => $validated['attachments'] ?? [],
        ]);

        $this->reset(['message', 'attachments']);
        $this->record->refresh();

        $this->dispatch('ticket-chat-scroll');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'attachments.*.mimes' => 'Solo se permiten archivos JPG y PNG.',
            'attachments.*.max' => 'Cada archivo debe pesar como máximo 2 MB.',
        ];
    }

    public function canSendMessages(): bool
    {
        return auth()->user()->can('addComment', $this->record->fresh());
    }

    public function refreshMessages(): void
    {
        $this->record->refresh();
    }

    public function render(): View
    {
        $messages = $this->record
            ->comments()
            ->with(['user', 'attachments'])
            ->oldest()
            ->get();

        return view('livewire.tickets.ticket-chat', [
            'messages' => $messages,
            'canSendMessages' => $this->canSendMessages(),
        ]);
    }
}
