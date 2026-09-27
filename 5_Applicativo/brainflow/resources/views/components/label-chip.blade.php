@props(['label', 'deletable' => false, 'deleteRoute' => null])

<span class="chip">
    <span class="chip__dot" style="background:{{ $label['color'] ?? $label->color ?? '#6366f1' }}"></span>
    {{ $label['name'] ?? $label->name ?? '' }}
    @if($deletable && $deleteRoute)
        <form action="{{ $deleteRoute }}" method="POST" style="display:inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn--ghost btn--icon" style="width:14px;height:14px;padding:0" title="Rimuovi">
                <x-icon name="close" size="sm" />
            </button>
        </form>
    @endif
</span>
