<x-guest-layout>
    <div>
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

            .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }
        </style>
    @vite(['resources/js/app.js'])
    <div class="flex items-center justify-center px-4">
        <section class="w-full max-w-3xl rounded-3xl border border-white/10 bg-white/60 p-8 shadow-2xl backdrop-blur max-h-[75vh] overflow-y-auto hide-scrollbar">
            <div class="mb-8 space-y-2">
                <img src="{{ asset('images/bfar-logo.png') }}" alt="BFAR" class="block mx-auto h-16 w-auto object-contain drop-shadow-md sm:h-18 lg:h-24">
                <h2 class="text-3xl font-semibold text-slate-900 text-center">Sign up for BFAR XII Portal</h2>
                <p class="text-sm text-slate-500 text-center">Fill in your details to get started.</p>
            </div>

            <form id="register-form" method="POST" 
                action="{{ route('register', [], false) }}" 
                class="space-y-6"
                enctype="multipart/form-data"
                x-data="formCache()"
                @submit.prevent="handleRegisterSubmit">
                @csrf

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

                <!-- Loading Spinner Component -->
                <div x-show="isLoading" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-end justify-center  ">
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
                            <p class="text-gray-700 font-medium">Creating your account...</p>
                            <p class="text-gray-500 text-sm mt-1">Please wait...</p>
                        </div>
                    </div>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="text-md font-medium text-slate-700">{{ __('Full name') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                        <input id="name" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0 uppercase" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Santos, Paulino S." oninput="this.value = this.value.toUpperCase();" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="text-md font-medium text-slate-700">{{ __('Email') }}</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
                            <input id="email" 
                            class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="name@bfar.gov.ph" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="text-md font-medium text-slate-700">Phone</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone-icon lucide-phone"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/></svg>
                            <input id="phone" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="09123456789" required maxlength="11" pattern="\d{11}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" />
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Type --> 
                    <div>
                        <label for="type" class="text-md font-medium text-slate-700">Employment Status</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-search-icon lucide-user-round-search"><circle cx="10" cy="8" r="5"/><path d="M2 21a8 8 0 0 1 10.434-7.62"/><circle cx="18" cy="18" r="3"/><path d="m22 22-1.9-1.9"/></svg>  
                            <select id="type" name="type" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 focus:ring-0" required>
                                <option value="Permanent Employee" {{ old('type', 'Permanent Employee') === 'Permanent Employee' ? 'selected' : '' }}>Permanent Employee</option>
                                <option value="Contract of Service" {{ old('type') === 'Contract of Service' ? 'selected' : '' }}>Contract of Service</option>
                                <option value="Job Order" {{ old('type') === 'Job Order' ? 'selected' : '' }}>Job Order</option>
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="text-md font-medium text-slate-700">Sex</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                            <select id="gender" name="gender" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 focus:ring-0" required>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="text-md font-medium text-slate-700">{{ __('Password') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock-icon lucide-lock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input id="password" 
                        class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" type="password" name="password" required autocomplete="new-password" placeholder="Create a password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="text-md font-medium text-slate-700">{{ __('Confirm Password') }}</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock-icon lucide-lock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input id="password_confirmation" 
                        class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" 
                        type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter password" />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                    <!-- Unit -->
                    <div>
                        <label for="unit" class="text-md font-medium text-slate-700">Division/Section/Unit/Office</label>
                        <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2-icon lucide-building-2"><path d="M10 12h4"/><path d="M10 8h4"/><path d="M14 21v-3a2 2 0 0 0-4 0v3"/><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/></svg>
                            <select id="unit" name="unit" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0" required>
                                <option value="" disabled {{ old('unit') ? '' : 'selected' }}>Select Division/Section/Unit/Office</option>
                                @foreach (config('units.list', []) as $unit)
                                    <option value="{{ $unit }}" {{ old('unit') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                    </div>


                <!-- Location Assigned -->
                <div>
                    <label for="location_assigned" class="text-md font-medium text-slate-700">Place of Assignment</label>
                    <div class="mt-2 flex items-center rounded-2xl border border-slate-200 bg-white/20 px-4 py-3 ring-offset-2 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pinned-icon lucide-map-pinned"><path d="M18 8c0 3.613-3.869 7.429-5.393 8.795a1 1 0 0 1-1.214 0C9.87 15.429 6 11.613 6 8a6 6 0 0 1 12 0"/><circle cx="12" cy="8" r="2"/><path d="M8.714 14h-3.71a1 1 0 0 0-.948.683l-2.004 6A1 1 0 0 0 3 22h18a1 1 0 0 0 .948-1.316l-2-6a1 1 0 0 0-.949-.684h-3.712"/></svg>
                        <input id="location_assigned" class="ml-3 w-full border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 focus:ring-0 uppercase" type="text" name="location_assigned" value="{{ old('location_assigned') }}" placeholder="e.g., BFAR Regional HQ – Lagao, GenSan" required oninput="this.value = this.value.toUpperCase();" />
                    </div>
                    <x-input-error :messages="$errors->get('location_assigned')" class="mt-2" />
                </div>

                <!-- Profile Photo -->
                <div>
                    <label class="text-md font-medium text-slate-700" for="profile_photo">Profile Photo</label>

                    <div class="flex flex-col items-center gap-3">
                        <div class="relative w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 lg:w-64 lg:h-64 rounded-full overflow-hidden border border-gray-200 shadow-sm bg-white">
                            <!-- Live Camera -->
                            <video x-ref="video" class="absolute inset-0 h-full w-full object-cover" 
                                style="transform: scaleX(-1);" 
                                x-show="streaming" playsinline muted></video>

                            <!-- Progress ring on outer border -->
                            <div x-show="streaming"
                                class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <svg class="absolute inset-[-6px] w-[calc(100%+12px)] h-[calc(100%+12px)]" viewBox="0 0 112 112" aria-hidden="true">
                                    <circle cx="56" cy="56" r="52" stroke="rgba(255,255,255,0.25)" stroke-width="6" fill="none" />
                                    <circle cx="56" cy="56" r="52" stroke="#38bdf8" stroke-width="6" fill="none"
                                        stroke-linecap="round"
                                        stroke-dasharray="326.72"
                                        :stroke-dashoffset="`${326.72 * (1 - detectionProgress/100)}`"
                                        transform="rotate(-90 56 56)" />
                                </svg>
                                <div class="relative w-24 h-32 sm:w-32 sm:h-40 md:w-36 md:h-44">
                                    <div
                                        class="absolute inset-[6px] rounded-full bg-transparent border-4 border-dashed"
                                        :class="detectionState === 'ready'
                                            ? 'border-emerald-500 shadow-[0_0_0_2px_rgba(16,185,129,0.25)]'
                                            : 'border-rose-500 shadow-[0_0_0_2px_rgba(244,63,94,0.25)]'"
                                    ></div>
                                </div>
                            </div>

                            <!-- Image Preview -->
                            <template x-if="preview">
                                <img :src="preview" alt="Profile preview" 
                                    class="h-full w-full object-cover" />
                            </template>

                            <!-- Placeholder -->
                            <template x-if="!preview && !streaming">
                                <div class="flex h-full w-full items-center justify-center text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 sm:w-28 sm:h-28 md:w-36 md:h-36 lg:w-44 lg:h-44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                                </div>
                            </template>

                        </div>

                        <p x-text="detectionMessage"
                               x-show="detectionMessage"
                               :class="{
                                   'text-emerald-600': detectionState === 'ready',
                                   'text-rose-600': detectionState !== 'ready'
                               }"
                               class="text-xs font-semibold text-center px-4"></p>

                        <div class="flex flex-col items-center gap-2">
                            <input 
                                x-ref="uploadInput"
                                id="profile_photo_upload"
                                name="profile_photo"
                                type="file"
                                accept="image/*"
                                required
                                class="hidden"
                                @change="handlePhoto($event)">

                            <canvas x-ref="canvas" class="hidden"></canvas>

                            <div class="flex flex-wrap items-center justify-center gap-3">
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-400 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300" @click="startCamera()" x-show="!streaming">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 8h.01"/><path d="M17 6h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2"/><path d="m3 10 2.586-2.586a2 2 0 0 1 2.828 0L12 11l2.586-2.586a2 2 0 0 1 2.828 0L21 11"/><circle cx="12" cy="13" r="3"/></svg>
                                    Take Photo
                                </button>   
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-400 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300" @click="chooseUpload()" x-show="!streaming">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m19 12-7-7-7 7"/><path d="M5 19h14"/></svg>
                                    Upload Photo
                                </button>
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-emerald-500 bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300 disabled:opacity-60" @click="captureFrame()" x-show="streaming" :disabled="identifying || !modelsReady || detectionState !== 'ready'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/></svg>
                                    Capture
                                </button>
                            </div>

                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-transparent px-4 py-2 text-xs font-semibold text-slate-500 underline decoration-dashed decoration-slate-400 transition hover:text-rose-600" @click="clear()" x-show="preview || streaming">
                                Remove photo / stop camera
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 text-center pt-2">Use your device camera or upload a clear headshot. Square/circle framing shows how it will display.</p>
                    <x-input-error :messages="$errors->get('profile_photo')" class="mt-1" />
                </div>

                <button type="submit" 
                    class="group relative inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-500 via-sky-500 to-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-70"
                    :disabled="submitting">
                    <span class="absolute inset-0 rounded-2xl opacity-0 transition group-hover:opacity-20" style="background: linear-gradient(120deg, rgba(255,255,255,.7), rgba(255,255,255,0));"></span>
                    <span x-show="!submitting">{{ __('Create account') }}</span>
                    <span x-show="submitting" class="inline-flex items-center gap-2" aria-live="polite">
                        <svg class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" class="opacity-25"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" class="opacity-75"></path>
                        </svg>
                        {{ __('Creating...') }}
                    </span>
                </button>

                <div class="sticky bottom-0 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-md text-slate-600">
                    <p class="font-medium text-slate-800">Have an account already?</p>
                    <p class="mt-1 text-slate-600">
                        Log in with your BFAR account.
                        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-600 text-lg">Go to login →</a>
                    </p>
                </div>
                
            </form>
        </section>
    </div>

  

@if ($errors->any())
<script>
document.addEventListener("DOMContentLoaded", () => {

    let cache = JSON.parse(localStorage.getItem("register_cache") || "{}");

    @foreach ($errors->keys() as $field)
        delete cache["{{ $field }}"];
    @endforeach

    localStorage.setItem("register_cache", JSON.stringify(cache));

});
</script>
@endif

@if(session('clearRegisterCache'))
<script>
localStorage.removeItem("register_cache");
</script>
@endif

<script>
// Network error handling for registration form
function handleRegisterSubmit() {
    if (!navigator.onLine) {
        this.showNetworkError = true;
        return;
    }
    this.submitting = true;
    this.isLoading = true;
    this.$nextTick(() => {
        document.getElementById('register-form').submit();
    });
}
</script>
</x-guest-layout>


