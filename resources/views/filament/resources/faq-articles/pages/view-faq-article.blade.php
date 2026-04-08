@php
    $ticket = $this->record->ticket;
    $messages = $ticket?->comments ?? collect();
    $historyItems = $ticket?->ticketHistories ?? collect();

    $statusClasses = match ($ticket?->estado) {
        'activo' => 'bg-success-50 text-success-700 ring-1 ring-success-600/20 dark:bg-success-500/10 dark:text-success-300 dark:ring-success-400/20',
        'en_proceso' => 'bg-primary-50 text-primary-700 ring-1 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-400/20',
        'cerrado' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700',
        default => 'bg-gray-100 text-gray-700 ring-1 ring-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700',
    };

    $priorityClasses = match ($ticket?->prioridad) {
        'alta' => 'bg-danger-50 text-danger-700 ring-1 ring-danger-600/20 dark:bg-danger-500/10 dark:text-danger-300 dark:ring-danger-400/20',
        'media' => 'bg-warning-50 text-warning-700 ring-1 ring-warning-600/20 dark:bg-warning-500/10 dark:text-warning-300 dark:ring-warning-400/20',
        'baja' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700',
        default => 'bg-gray-100 text-gray-700 ring-1 ring-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700',
    };

    $label = fn (?string $value) => filled($value) ? str($value)->replace('_', ' ')->headline()->toString() : 'Sin dato';
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-xl border border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                        FAQ Article
                    </p>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-gray-50">
                        {{ $this->record->titulo }}
                    </h1>
                    <p class="max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $this->record->descripcion_problema ?: 'Vista consolidada del ticket que origino este articulo de conocimiento.' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-primary-50 px-4 py-2 text-sm font-semibold text-primary-700 ring-1 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-400/20">
                        {{ $this->getCategoryLabel() }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                        {{ $label($ticket?->estado) }}
                    </span>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-0 lg:grid-cols-3">
                <div class="border-b border-gray-200 p-6 dark:border-gray-800 lg:col-span-2 lg:border-b-0 lg:border-r">
                    <div class="mb-6 flex items-center gap-3">

                        <div>
                            <h2 class="text-lg font-semibold text-gray-950 dark:text-gray-50">Resolución del ticket</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Conversacion del ticket</p>
                        </div>
                    </div>

                    <div class="max-h-[38rem] space-y-4 overflow-y-auto pr-2">
                        @forelse ($messages as $message)
                            @php
                                $isTechnician = $message->rol === 'tecnico';
                                $rowClasses = $isTechnician ? 'justify-end' : 'justify-start';
                                $metaClasses = $isTechnician ? 'items-end text-right' : 'items-start text-left';
                                $bubbleClasses = $isTechnician
                                    ? 'bg-primary-500 text-white dark:bg-primary-600'
                                    : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100';
                                $attachmentClasses = $isTechnician
                                    ? 'border-white/20 bg-white/10 text-white hover:bg-white/15'
                                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800';
                            @endphp

                            <div class="flex {{ $rowClasses }}">
                                <div class="flex w-full max-w-2xl flex-col gap-2 {{ $metaClasses }}">
                                    <div class="space-y-1">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                                            {{ $isTechnician ? 'Tecnico' : 'Usuario' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $message->user?->name ?? 'Usuario eliminado' }}
                                            <span class="mx-1">·</span>
                                            {{ $message->created_at?->format('d/m/Y H:i') }}
                                        </p>
                                    </div>

                                    <div class="max-w-xl rounded-2xl px-4 py-4 shadow-sm ring-1 ring-black/5 dark:ring-white/10 {{ $bubbleClasses }}">
                                        <p class="whitespace-pre-line text-sm leading-6">
                                            {{ $message->contenido }}
                                        </p>

                                        @if ($message->attachments->isNotEmpty())
                                            <div class="mt-4 space-y-2">
                                                @foreach ($message->attachments as $attachment)
                                                    <a
                                                        href="{{ $attachment->getPublicUrl() }}"
                                                        target="_blank"
                                                        class="block rounded-xl border px-3 py-3 text-sm transition {{ $attachmentClasses }}"
                                                    >
                                                        @if ($attachment->isImage())
                                                            <img
                                                                src="{{ $attachment->getImageSource() }}"
                                                                alt="{{ $attachment->getDisplayName() }}"
                                                                class="mb-3 max-h-56 w-full rounded-lg object-cover"
                                                            >
                                                        @endif
                                                        <span class="font-medium">{{ $attachment->getDisplayName() }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center dark:border-gray-700 dark:bg-gray-800/50">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    No hay comentarios registrados para este ticket.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <aside class="p-6 lg:col-span-1">
                    <div class="mb-6 flex items-center gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-950 dark:text-gray-50">Informacion del ticket</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Contexto del caso</p>
                        </div>
                    </div>

                    <dl class="space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Titulo</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket?->titulo ?? 'Sin ticket relacionado' }}</dd>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</dt>
                            <dd class="mt-2">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $label($ticket?->estado) }}
                                </span>
                            </dd>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tecnico</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket?->technician?->name ?? 'Sin tecnico asignado' }}</dd>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Usuario</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket?->user?->name ?? 'Sin usuario' }}</dd>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Prioridad</dt>
                            <dd class="mt-2">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClasses }}">
                                    {{ $label($ticket?->prioridad) }}
                                </span>
                            </dd>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Categoria</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label($ticket?->categoria) }}</dd>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Creado</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket?->fecha_creacion?->format('d/m/Y H:i') ?? 'Sin fecha' }}</dd>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/60">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cerrado</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket?->fecha_cierre?->format('d/m/Y H:i') ?? 'Aun abierto' }}</dd>
                        </div>
                    </dl>
                </aside>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950 dark:text-gray-50">Historial</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Timeline del ticket relacionado</p>
                    </div>
                </div>
            </div>

            <div class="max-h-[32rem] overflow-y-auto px-6 py-6 pr-4">
                @forelse ($historyItems as $item)
                    <div class="relative flex gap-4 pb-8 last:pb-0">
                        @unless ($loop->last)
                            <div class="absolute left-[0.4375rem] top-4 h-full border-l border-gray-300 dark:border-gray-700"></div>
                        @endunless

                        <div class="relative mt-1 h-4 w-4 flex-none rounded-full bg-primary-500 ring-4 ring-primary-100 dark:bg-primary-500 dark:ring-primary-500/20"></div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ str($item->accion)->replace('_', ' ')->headline() }}
                            </p>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                {{ $item->comentario ?: 'Sin descripcion adicional.' }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $item->user?->name ?? 'Sistema' }} · {{ $item->created_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center dark:border-gray-700 dark:bg-gray-800/50">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            No hay eventos registrados en el historial de este ticket.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
