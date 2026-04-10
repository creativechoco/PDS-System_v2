<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-4xl space-y-8">
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 space-y-6">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Admin Profile</h1>
                    <p class="text-sm text-slate-600">Update your name and email. Other employee fields are hidden for admins.</p>
                </div>

                @if (session('status') === 'profile-updated')
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Profile updated successfully.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="name">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $admin->name) }}" required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        @error('name')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        @error('email')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Save
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Update Password</h2>
                    <p class="text-sm text-slate-600">Use a strong password to keep your account secure.</p>
                </div>

                @if (session('status') === 'password-updated')
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Password updated successfully.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="current_password">Current Password</label>
                        <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        @error('current_password')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="password">New Password</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        @error('password')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
