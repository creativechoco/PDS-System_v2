<x-guest-layout>
    <div x-data="{ isLoading: false, showNetworkError: false }">
        <!-- Network Error Modal -->
        <div x-show="showNetworkError" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl p-8 shadow-2xl max-w-md mx-4 flex flex-col items-center space-y-4">
                <!-- Error Icon -->
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <!-- Error Message -->
                <div class="text-center">
                    <h3 class="text-xl font-semibold text-gray-900">No Internet Connection</h3>
                    <p class="text-gray-600 mt-2">Please check your internet connection and try again.</p>
                </div>

                <!-- Retry Button -->
                <button @click="showNetworkError = false" class="w-full inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-500 to-sky-500 px-6 py-3 text-base font-semibold text-white shadow-lg transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-500">
                    Try Again
                </button>
            </div>
        </div>

    @if ($errors->has('email'))
        <div id="verify-error-modal" class="fixed inset-0 z-30 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4">
            <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl ring-1 ring-rose-200 text-center p-8 space-y-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m15 9-6 6m0-6 6 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Verification expired</p>
                    <h3 class="text-xl font-semibold text-slate-900">This verification link is invalid or has expired.</h3>
                    <p class="text-sm text-slate-600">Please resend a new activation link and use the latest email we sent.</p>
                </div>
                <button id="dismiss-verify-error" type="button" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2">Got it</button>
            </div>
        </div>
    @endif

    <div class="fixed inset-0 z-10 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
            <!-- Header -->
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 11 2.5 2.5L16 8"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Activation sent</p>
                    <h3 class="text-lg font-semibold text-slate-900">Check your email</h3>
                </div>
            </div>

            <!-- Content -->
            <div class="space-y-3 px-6 py-5 text-sm text-slate-700">
                <p>We emailed an activation link to the address you provided. Please open the email and click the link to activate your account.</p>
                <p class="text-slate-600">Once activated, you'll be taken straight to your dashboard. You can open the link from any device—this page will auto-redirect once your email is verified. If you don't see it, check spam or resend below.</p>

                @if (session('status') == 'verification-link-sent')
                    <div class="rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700">A fresh verification link was sent.</div>
                @elseif (session('status') == 'verification-email-updated')
                    <div class="rounded-xl bg-emerald-50 px-4 py-3 text-emerald-700">Email updated. A new verification link was sent.</div>
                @elseif (session('status') == 'registration-successful-email-failed')
                    <div class="rounded-xl bg-amber-50 px-4 py-3 text-amber-700">
                        <p class="font-semibold">Registration successful, but email could not be sent.</p>
                        <p class="text-sm mt-1">Please check your internet connection and try resending the verification email below.</p>
                    </div>
                @endif

                <div id="status-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-rose-700"></div>

                <!-- Update Email -->
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-800">Entered the wrong email?</p>
                    <p class="text-xs text-slate-600">Update your email and we will send a new activation link.</p>
                    <form method="POST" action="{{ route('verification.update', [], false) }}" class="mt-3 space-y-2" id="update-email-form" @submit.prevent="handleUpdateEmail">
                        @csrf
                        <label for="new_email" class="text-xs font-medium text-slate-700">New email</label>
                        <input id="new_email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required
                               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        @error('email')
                            <div class="text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                        <button id="update-email-btn" type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                            Update email & send link
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 px-6 py-4">
                <form method="POST" action="{{ route('verification.send', [], false) }}" class="flex-1 min-w-[10rem]" id="resend-form" @submit.prevent="handleResend">
                    @csrf
                    <button id="resend-btn" type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4 8 7.338 4.669a2 2 0 0 0 2.152 0L21 8"/>
                        </svg>
                        <span id="resend-label">Resend verification email</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout', [], false) }}" id="logout-form">
                    @csrf
                    <button type="submit" id="logout-btn" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        (() => {
            const statusUrl = '{{ route('verification.status', [], false) }}';
            const buildStatusUrl = () => `${statusUrl}?t=${Date.now()}`; // bust caches
            // Redirect immediately after verification to avoid perceived lag
            const REDIRECT_DELAY_MS = 100; // Reduced to 100ms for smoother transition
            const RESEND_KEY = 'verify_resend_at';
            const COOLDOWN_MS = 3 * 60 * 1000; // 3 minutes
            const resendBtn = document.getElementById('resend-btn');
            const resendLabel = document.getElementById('resend-label');
            const resendForm = document.getElementById('resend-form');
            const updateEmailInput = document.getElementById('new_email');
            const updateEmailBtn = document.getElementById('update-email-btn');
            const logoutForm = document.getElementById('logout-form');
            const logoutBtn = document.getElementById('logout-btn');
            const initialEmail = updateEmailInput ? updateEmailInput.value.trim() : '';
            const errorModal = document.getElementById('verify-error-modal');
            const dismissErrorBtn = document.getElementById('dismiss-verify-error');

            let redirecting = false;
            let eventSource = null;

            // Disable update email button unless value changed
            function syncUpdateEmailState() {
                if (!updateEmailInput || !updateEmailBtn) return;
                const current = updateEmailInput.value.trim();
                const changed = current.toLowerCase() !== initialEmail.toLowerCase();
                updateEmailBtn.disabled = !changed;
            }

            if (updateEmailInput) {
                updateEmailInput.addEventListener('input', syncUpdateEmailState);
                syncUpdateEmailState();
            }

            if (dismissErrorBtn && errorModal) {
                dismissErrorBtn.addEventListener('click', () => {
                    errorModal.remove();
                });
            }

            // Update button state based on cooldown
            function updateResendState() {
                const last = parseInt(localStorage.getItem(RESEND_KEY) || '0', 10);
                const now = Date.now();
                const remaining = last + COOLDOWN_MS - now;

                if (remaining > 0) {
                    resendBtn.disabled = true;
                    resendBtn.classList.add('opacity-70', 'cursor-not-allowed');
                    const secs = Math.ceil(remaining / 1000);
                    resendLabel.textContent = `Resend available in ${secs}s`;
                } else {
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                    resendLabel.textContent = 'Resend verification email';
                }
            }

            // Handle form submit
            if (resendForm) {
                resendForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    
                    // Prevent multiple submissions
                    if (resendBtn.disabled) {
                        return false;
                    }
                    
                    // Check network connectivity
                    if (!navigator.onLine) {
                        const alpineEl = document.querySelector('[x-data]');
                        if (alpineEl && alpineEl.__x) {
                            alpineEl.__x.$data.showNetworkError = true;
                        }
                        return false;
                    }
                    
                    // Set cooldown and disable button
                    localStorage.setItem(RESEND_KEY, Date.now().toString());
                    updateResendState();
                    
                    // Allow form to submit normally
                    resendForm.submit();
                });
            }

            // Handle update email form
            const updateEmailForm = document.getElementById('update-email-form');
            if (updateEmailForm) {
                updateEmailForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    
                    // Check network connectivity
                    if (!navigator.onLine) {
                        const alpineEl = document.querySelector('[x-data]');
                        if (alpineEl && alpineEl.__x) {
                            alpineEl.__x.$data.showNetworkError = true;
                        }
                        return false;
                    }
                    
                    // Allow form to submit normally
                    updateEmailForm.submit();
                });
            }

            // Prevent multiple logout submissions
            if (logoutForm && logoutBtn) {
                logoutForm.addEventListener('submit', (event) => {
                    logoutBtn.disabled = true;
                    logoutBtn.textContent = 'Logging out...';
                    // Allow form to submit normally
                });
            }

            // Run every second to update label
            setInterval(updateResendState, 1000);
            updateResendState();

            const currentUserId = {{ auth()->id() ?? 'null' }};

            // Use Server-Sent Events (SSE) for automatic redirection without polling
            if (currentUserId) {
                eventSource = new EventSource('{{ route("verification.stream", [], false) }}');
                
                eventSource.onmessage = function(event) {
                    const data = JSON.parse(event.data);
                    console.debug('SSE verification update received', data);
                    
                    if (data.verified && !redirecting) {
                        redirecting = true;
                        eventSource.close();
                        
                        const target = data.redirect || '{{ route('dashboard', [], false) }}';
                        const overlay = document.createElement('div');
                        overlay.className = 'fixed inset-0 z-20 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm text-white text-center p-6 transition-opacity duration-500 ease-out opacity-0';
                        overlay.innerHTML = `
                            <div class="flex flex-col items-center gap-4">
                                <div class="h-14 w-14 rounded-full border-3 border-white/30 border-t-white animate-spin"></div>
                                <div class="space-y-1">
                                    <h3 class="text-xl font-semibold">Redirecting...</h3>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(overlay);
                        requestAnimationFrame(() => overlay.classList.remove('opacity-0'));
                        setTimeout(() => {
                            window.location.href = target;
                        }, REDIRECT_DELAY_MS);
                    }
                };
                
                eventSource.onerror = function(event) {
                    console.warn('SSE connection error, retrying...', event);
                    // Auto-reconnect on error
                    setTimeout(() => {
                        if (!redirecting && eventSource.readyState === EventSource.CLOSED) {
                            eventSource = new EventSource('{{ route("verification.stream", [], false) }}');
                        }
                    }, 3000);
                };
                
                console.debug('SSE connection established for verification monitoring');
            } else {
                console.warn('User not authenticated for SSE verification monitoring');
            }

            // Alpine.js functions for form handling
            function handleResend() {
                if (!navigator.onLine) {
                    this.showNetworkError = true;
                    return;
                }
                if (resendBtn.disabled) {
                    return;
                }
                localStorage.setItem(RESEND_KEY, Date.now().toString());
                updateResendState();
                this.$nextTick(() => {
                    resendForm.submit();
                });
            }

            function handleUpdateEmail() {
                if (!navigator.onLine) {
                    this.showNetworkError = true;
                    return;
                }
                this.$nextTick(() => {
                    updateEmailForm.submit();
                });
            }
        })();
    </script>
    </div>
</x-guest-layout>