<x-guest-layout>
    <div class="fixed inset-0 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl ring-1 ring-rose-200 text-center p-8 space-y-4">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m15 9-6 6m0-6 6 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Verification failed</p>
                <h3 class="text-xl font-semibold text-slate-900">This verification link is invalid or has expired.</h3>
                <p class="text-sm text-slate-600">Please go back to the original tab and resend a new activation link, then use the latest email.</p>
            </div>
        </div>
    </div>
</x-guest-layout>
