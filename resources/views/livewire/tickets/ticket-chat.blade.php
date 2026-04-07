<div wire:poll.3s="refreshMessages">
<style>
    .ticket-chat {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .ticket-chat__messages {
        max-height: 34rem;
        overflow-y: auto;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 1.5rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .ticket-chat__row {
        display: flex;
    }

    .ticket-chat__row--technician {
        justify-content: flex-start;
    }

    .ticket-chat__row--user {
        justify-content: flex-end;
    }

    .ticket-chat__message {
        display: flex;
        max-width: 42rem;
        width: 100%;
        flex-direction: column;
        gap: 0.45rem;
    }

    .ticket-chat__message--technician {
        align-items: flex-start;
    }

    .ticket-chat__message--user {
        align-items: flex-end;
    }

    .ticket-chat__meta {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
    }

    .ticket-chat__meta--technician {
        text-align: left;
    }

    .ticket-chat__meta--user {
        text-align: right;
    }

    .ticket-chat__bubble {
        max-width: min(100%, 36rem);
        border-radius: 1.5rem;
        padding: 1rem 1.1rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }

    .ticket-chat__bubble--technician {
        background: #e2e8f0;
        color: #0f172a;
    }

    .ticket-chat__bubble--user {
        background: #ffb900;
        color: #7b3306;
    }

    .ticket-chat__role {
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .ticket-chat__role--technician {
        color: #475569;
    }

    .ticket-chat__role--user {
        color: #7b3306;
    }

    .ticket-chat__role-dot {
        display: inline-flex;
        width: 0.65rem;
        height: 0.65rem;
        border-radius: 9999px;
    }

    .ticket-chat__role-dot--technician {
        background: #64748b;
    }

    .ticket-chat__role-dot--user {
        background: #7b3306;
    }

    .ticket-chat__content {
        margin: 0;
        white-space: pre-line;
        font-size: 0.95rem;
        line-height: 1.65;
    }

    .ticket-chat__attachments {
        margin-top: 0.9rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .ticket-chat__attachment-image-link,
    .ticket-chat__attachment-file {
        display: block;
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.25);
        text-decoration: none;
    }

    .ticket-chat__attachment-image-link--technician,
    .ticket-chat__attachment-file--technician {
        background: #ffffff;
        color: #0f172a;
    }

    .ticket-chat__attachment-image-link--user,
    .ticket-chat__attachment-file--user {
        background: rgba(123, 51, 6, 0.16);
        color: #ffffff;
    }

    .ticket-chat__attachment-image {
        display: block;
        max-height: 16rem;
        width: 100%;
        object-fit: cover;
    }

    .ticket-chat__attachment-name {
        font-size: 0.75rem;
    }

    .ticket-chat__attachment-name--technician {
        color: #475569;
    }

    .ticket-chat__attachment-name--user {
        color: #7b3306;
    }

    .ticket-chat__attachment-file {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.7rem 0.85rem;
        font-size: 0.9rem;
    }

    .ticket-chat__empty {
        border: 1px dashed #cbd5e1;
        border-radius: 1.5rem;
        background: #f8fafc;
        padding: 2.5rem 1.5rem;
        text-align: center;
        font-size: 0.9rem;
        color: #64748b;
    }

    .ticket-chat__form {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 1.5rem;
        background: #ffffff;
        padding: 1rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    }

    .ticket-chat__textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 1rem;
        padding: 0.9rem 1rem;
        font-size: 0.95rem;
        color: #0f172a;
        background: #ffffff;
    }

    .ticket-chat__textarea:disabled {
        background: #e2e8f0;
        cursor: not-allowed;
    }

    .ticket-chat__file-input {
        display: block;
        width: 100%;
        font-size: 0.9rem;
        color: #475569;
    }

    .ticket-chat__loading {
        font-size: 0.9rem;
        color: #64748b;
    }

    .ticket-chat__actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .ticket-chat__submit {
        display: inline-flex;
        align-items: center;
        border: 0;
        border-radius: 0.9rem;
        background: #ffb900;
        color: #000000;
        padding: 0.7rem 1.25rem;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
    }

    .dark .ticket-chat__submit {
        display: inline-flex;
        align-items: center;
        border: 0;
        border-radius: 0.9rem;
        background: #e17100;
        color: #000000;
        padding: 0.7rem 1.25rem;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
    }

    .ticket-chat__submit:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .dark .ticket-chat__messages {
        border-color: rgba(255, 255, 255, 0.08);
        background: #2222275c;
    }

    .dark .ticket-chat__meta {
        color: #94a3b8;
    }

    .dark .ticket-chat__bubble--technician {
        background: #242427;
        color: #e2e8f0;
    }

    .dark .ticket-chat__bubble--user {
        background: #e17100;
        color: #222121;
    }

    .dark .ticket-chat__role--technician {
        color: #cbd5e1;
    }

    .dark .ticket-chat__role--user {
        color: #222121;
    }

    .dark .ticket-chat__role-dot--user {
        background: #222121;
    }

    .dark .ticket-chat__role-dot--technician {
        background: #cbd5e1;
    }

    .dark .ticket-chat__attachment-image-link--technician,
    .dark .ticket-chat__attachment-file--technician {
        background: #0f172a;
        color: #e2e8f0;
        border-color: rgba(148, 163, 184, 0.22);
    }

    .dark .ticket-chat__attachment-image-link--user,
    .dark .ticket-chat__attachment-file--user {
        background: rgba(15, 23, 42, 0.35);
        border-color: rgba(191, 219, 254, 0.18);
    }

    .dark .ticket-chat__attachment-name--technician {
        color: #cbd5e1;
    }

    .dark .ticket-chat__attachment-name--user {
        color: #1d1d1d;
    }

    .dark .ticket-chat__empty {
        border-color: #334155;
        background: #0f172a;
        color: #94a3b8;
    }

    .dark .ticket-chat__form {
        border-color: rgba(255, 255, 255, 0.08);
        background: #1b1b1f;
    }

    .dark .ticket-chat__textarea {
        border-color: #334155;
        background: #242427;
        color: #e2e8f0;
    }

    .dark .ticket-chat__textarea:disabled {
        background: #1e293b;
    }

    .dark .ticket-chat__file-input,
    .dark .ticket-chat__loading {
        color: #94a3b8;
    }
 </style>

<div
    x-data="{
        scrollToBottom() {
            if (this.$refs.messages) {
                this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight
            }
        },
        init() {
            this.$nextTick(() => this.scrollToBottom())
            window.addEventListener('ticket-chat-scroll', () => this.$nextTick(() => this.scrollToBottom()))
        },
    }"
    x-init="init()"
    x-effect="$nextTick(() => scrollToBottom())"
    class="ticket-chat"
>
    <div
        x-ref="messages"
        class="ticket-chat__messages"
    >
        @forelse ($messages as $message)
            @php
                $isTechnician = $message->rol === 'tecnico';
            @endphp

            <div
                wire:key="message-{{ $message->id }}"
                class="ticket-chat__row {{ $isTechnician ? 'ticket-chat__row--technician' : 'ticket-chat__row--user' }}"
            >
                <div class="ticket-chat__message {{ $isTechnician ? 'ticket-chat__message--technician' : 'ticket-chat__message--user' }}">
                    <div class="ticket-chat__meta {{ $isTechnician ? 'ticket-chat__meta--technician' : 'ticket-chat__meta--user' }}">
                        {{ $message->user?->name ?? 'Usuario eliminado' }}
                        ·
                        {{ $message->created_at?->format('d/m/Y H:i') }}
                    </div>

                    <div class="ticket-chat__bubble {{ $isTechnician ? 'ticket-chat__bubble--technician' : 'ticket-chat__bubble--user' }}">
                        <div class="ticket-chat__role {{ $isTechnician ? 'ticket-chat__role--technician' : 'ticket-chat__role--user' }}">
                            <span class="ticket-chat__role-dot {{ $isTechnician ? 'ticket-chat__role-dot--technician' : 'ticket-chat__role-dot--user' }}"></span>
                            {{ $isTechnician ? 'Técnico' : 'Usuario' }}
                        </div>

                        <p class="ticket-chat__content">{{ $message->contenido }}</p>

                        @if ($message->attachments->isNotEmpty())
                            <div class="ticket-chat__attachments">
                                @foreach ($message->attachments as $attachment)
                                    @if ($attachment->isImage())
                                        <a
                                            href="{{ $attachment->getPublicUrl() }}"
                                            target="_blank"
                                            class="ticket-chat__attachment-image-link {{ $isTechnician ? 'ticket-chat__attachment-image-link--technician' : 'ticket-chat__attachment-image-link--user' }}"
                                        >
                                            <img
                                                src="{{ $attachment->getImageSource() }}"
                                                alt="{{ $attachment->getDisplayName() }}"
                                                loading="lazy"
                                                class="ticket-chat__attachment-image"
                                            >
                                        </a>
                                        <div class="ticket-chat__attachment-name {{ $isTechnician ? 'ticket-chat__attachment-name--technician' : 'ticket-chat__attachment-name--user' }}">
                                            {{ $attachment->getDisplayName() }}
                                        </div>
                                    @else
                                        <a
                                            href="{{ $attachment->getPublicUrl() }}"
                                            target="_blank"
                                            class="ticket-chat__attachment-file {{ $isTechnician ? 'ticket-chat__attachment-file--technician' : 'ticket-chat__attachment-file--user' }}"
                                        >
                                            <span style="font-weight: 600;">{{ $attachment->getDisplayName() }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="ticket-chat__empty">
                Aún no hay mensajes en esta conversación.
            </div>
        @endforelse
    </div>

    <form wire:submit="sendMessage" class="ticket-chat__form">
        <textarea
            wire:model="message"
            rows="4"
            placeholder="Escribe un mensaje..."
            @disabled(! $canSendMessages)
            class="ticket-chat__textarea"
        ></textarea>
        @error('message')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="space-y-2">
            <input
                type="file"
                wire:model="attachments"
                multiple
                accept="image/png,image/jpeg"
                @disabled(! $canSendMessages)
                class="ticket-chat__file-input"
            >
            @error('attachments.*')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div wire:loading wire:target="attachments" class="ticket-chat__loading">
                Cargando archivos...
            </div>
        </div>

        @unless ($canSendMessages)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                La conversación está deshabilitada para este ticket o para tu rol actual.
            </div>
        @endunless

        <div class="ticket-chat__actions">
            <button
                type="submit"
                @disabled(! $canSendMessages)
                wire:loading.attr="disabled"
                wire:target="sendMessage,attachments"
                class="ticket-chat__submit"
            >
                Enviar
            </button>
        </div>
    </form>
</div>
</div>
