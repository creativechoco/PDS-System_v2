<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form id="profile-update-form" method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full uppercase" :value="old('name', $user->name)" required autofocus autocomplete="name" oninput="this.value = this.value.toUpperCase();" x-bind:readonly="!editable" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" x-bind:readonly="!editable" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>    

            <div>
                <x-input-label for="phone" :value="__('Phone Number')" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" required maxlength="11" pattern="\d{11}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" x-bind:readonly="!editable" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="gender" :value="__('Sex')" />
                <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required x-bind:disabled="!editable">
                    <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>

            <div>
                <x-input-label for="type" :value="__('Employment Status')" />
                <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required x-bind:disabled="!editable">
                    <option value="Permanent Employee" {{ old('type', $user->type) === 'Permanent Employee' ? 'selected' : '' }}>Permanent Employee</option>
                    <option value="Contract of Service" {{ old('type', $user->type) === 'Contract of Service' ? 'selected' : '' }}>Contract of Service</option>
                    <option value="Job Order" {{ old('type', $user->type) === 'Job Order' ? 'selected' : '' }}>Job Order</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('type')" />
            </div>
        </div>  
        
        

        <div>
            <x-input-label for="unit" :value="__('Division/Section/Unit/Office')" />
            <select id="unit" name="unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required x-bind:disabled="!editable">
                <option value="" disabled {{ old('unit', $user->unit) ? '' : 'selected' }}>Select Division/Section/Unit/Office</option>
                @foreach ($units ?? config('units.list', []) as $unit)
                    <option value="{{ $unit }}" {{ old('unit', $user->unit) === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
        </div>

        <div>
            <x-input-label for="location_assigned" :value="__('Place of Assignment')" />
            <x-text-input id="location_assigned" name="location_assigned" type="text" class="mt-1 block w-full uppercase" :value="old('location_assigned', $user->location_assigned)" required oninput="this.value = this.value.toUpperCase();" x-bind:readonly="!editable" />
            <x-input-error class="mt-2" :messages="$errors->get('location_assigned')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button x-show="editable" x-cloak>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved') }}</p>
            @endif
        </div>
    </form>
</section>
