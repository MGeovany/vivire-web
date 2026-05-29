{{-- Sonner toasts (public/js/toast.js). Include AFTER @livewireScripts. --}}
@if (session('status') || session('error'))
<script id="vivire-flash" type="application/json">@json([
    'status' => session('status'),
    'error' => session('error'),
])</script>
@endif
