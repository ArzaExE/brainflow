<div
    x-data="toastManager()"
    @toast.window="addToast($event.detail)"
    class="toasts"
    style="display:none"
    x-show="toasts.length > 0"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            class="toast"
            :class="{
                'toast--success': toast.type === 'success',
                'toast--danger':  toast.type === 'danger',
                'toast--warn':    toast.type === 'warn',
            }"
        >
            <span x-text="toast.message"></span>
            <button class="btn btn--ghost btn--icon" style="color:inherit;opacity:0.7" @click="removeToast(toast.id)">
                <x-icon name="close" size="sm" />
            </button>
        </div>
    </template>
</div>

{{-- Flash messages from Laravel session --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', message: '{{ addslashes(session('success')) }}' }
            }));
        });
    </script>
@endif
@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'danger', message: '{{ addslashes(session('error')) }}' }
            }));
        });
    </script>
@endif
@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'warn', message: '{{ addslashes(session('warning')) }}' }
            }));
        });
    </script>
@endif
