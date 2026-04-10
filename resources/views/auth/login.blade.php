<x-guest-layout>
    <div x-data="{ isLoading: false, showNetworkError: false }">
        <!-- Custom Animation Styles -->
        <style>
            @keyframes pulseRingA { 0%,4%{stroke-dasharray:0 660;stroke-width:20;stroke-dashoffset:-330;}12%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-335;}32%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-595;}40%,54%{stroke-dasharray:0 660;stroke-width:20;stroke-dashoffset:-660;}62%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-665;}82%{stroke-dasharray:60 600;stroke-width:30;stroke-dashoffset:-925;}90%,100%{stroke-dasharray:0 660;stroke-width:20;stroke-dashoffset:-990;} }

            @keyframes pulseRingB { 0%,12%{stroke-dasharray:0 220;stroke-width:20;stroke-dashoffset:-110;}20%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-115;}40%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-195;}48%,62%{stroke-dasharray:0 220;stroke-width:20;stroke-dashoffset:-220;}70%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-225;}90%{stroke-dasharray:20 200;stroke-width:30;stroke-dashoffset:-305;}98%,100%{stroke-dasharray:0 220;stroke-width:20;stroke-dashoffset:-330;} }

            @keyframes pulseRingC { 0%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:0;}8%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-5;}28%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-175;}36%,58%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-220;}66%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-225;}86%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-395;}94%,100%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-440;} }

            @keyframes pulseRingD { 0%,8%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:0;}16%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-5;}36%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-175;}44%,50%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-220;}58%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-225;}78%{stroke-dasharray:40 400;stroke-width:30;stroke-dashoffset:-395;}86%,100%{stroke-dasharray:0 440;stroke-width:20;stroke-dashoffset:-440;} }

            .animate-pulse-ring-a { animation: pulseRingA 2s linear infinite; }
            .animate-pulse-ring-b { animation: pulseRingB 2s linear infinite; }
            .animate-pulse-ring-c { animation: pulseRingC 2s linear infinite; }
            .animate-pulse-ring-d { animation: pulseRingD 2s linear infinite; }
        </style>
        
        <div class="flex items-center justify-center px-4">
            
            <section class="w-full max-w-xl rounded-3xl border border-white/10 bg-white/60 p-8 shadow-2xl backdrop-blur">
                <!-- Loading Spinner Component -->
                <div x-show="isLoading" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center ">
                    <div class="bg-white rounded-2xl p-8 shadow-2xl flex flex-col items-center space-y-4">
                        <!-- Loading Spinner SVG -->
                        <div class="relative">
                            <svg class="w-24 h-24" viewBox="0 0 240 240">
                                <circle class="animate-pulse-ring-a" cx="120" cy="120" r="105" fill="none" stroke="#2cab4f" stroke-width="20" stroke-dasharray="0 660" stroke-dashoffset="-330" stroke-linecap="round"></circle>
                                <circle class="animate-pulse-ring-b" cx="120" cy="120" r="35" fill="none" stroke="#f49725" stroke-width="20" stroke-dasharray="0 220" stroke-dashoffset="-110" stroke-linecap="round"></circle>
                                <circle class="animate-pulse-ring-c" cx="85" cy="120" r="70" fill="none" stroke="#255ff4" stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
                                <circle class="animate-pulse-ring-d" cx="155" cy="120" r="70" fill="none" stroke="#f42f25" stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
                            </svg>
                        </div>
                        
                        <!-- Loading Message -->
                        <div class="text-center">
                            <p class="text-gray-700 font-medium">Signing in to BFAR XII Portal...</p>
                            <p class="text-gray-500 text-sm mt-1">Please wait...</p>
                        </div>
                    </div>
                </div>

                <!-- Network Error Modal -->
                <div x-show="showNetworkError" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm rounded-3xl">
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

                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

            <div class="mb-4 space-y-2">
                <img src="{{ asset('images/bfar-logo.png') }}" alt="BFAR" class="block mx-auto h-16 w-auto object-contain drop-shadow-md sm:h-18 lg:h-24">
                <h2 class="text-center text-3xl font-semibold text-slate-900">Sign in to BFAR XII Portal</h2>
                <p class="text-center text-sm text-slate-500">Use your official BFAR email account to continue.</p>
            </div>

            <form id="login-form" class="space-y-6" method="POST" action="{{ route('login', [], false) }}" @submit.prevent="handleLogin">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="text-md font-medium text-slate-700">{{ __('Email') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l7.89 5.26c.68.45 1.54.45 2.22 0L21 8"/><path d="M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/></svg>
                        <input id="email" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@email.com" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="text-md font-medium text-slate-700">{{ __('Password') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="10" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input id="password" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="password" name="password" required autocomplete="current-password" placeholder="Enter password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
                    <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" name="remember">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="font-medium text-emerald-700 transition hover:text-emerald-600" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <button id="login-submit" type="submit" class="text-lg group relative inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-500 via-sky-500 to-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-500" :disabled="isLoading" :class="{ 'opacity-50 cursor-not-allowed': isLoading }">
                    <span class="absolute inset-0 rounded-2xl opacity-0 transition group-hover:opacity-20" style="background: linear-gradient(120deg, rgba(255,255,255,.7), rgba(255,255,255,0));"></span>
                    <template x-if="!isLoading">
                        <span>{{ __('Log in') }}</span>
                    </template>
                    <template x-if="isLoading">
                        <span>{{ __('Signing in...') }}</span>
                    </template>
                </button>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-md text-slate-600">
                    <p class="font-medium text-slate-800">Don't have an account yet?</p>
                    <p class="mt-1 text-slate-600">
                        It only takes a minute to set up your profile.
                        <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:text-emerald-600 text-lg">Sign up here →</a>
                    </p>
                </div>
            </form>
        </section>
    </div>

    <script>
        function handleLogin() {
            if (!navigator.onLine) {
                this.showNetworkError = true;
                return;
            }

            this.isLoading = true;
            this.$nextTick(() => {
                document.getElementById('login-form').submit();
            });
        }
    </script>
    </div>
</x-guest-layout>
