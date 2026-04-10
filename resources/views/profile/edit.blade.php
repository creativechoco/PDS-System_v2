<x-app-layout>
    @php
        $isAdmin = auth('admin')->check();
    @endphp

    @if ($isAdmin)
        <script>window.location.replace(@json(route('admin.profile.edit')));</script>
        
    @else
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" x-data="{ editable: @json($editAllowed ?? false) }">
                <form id="profile-request-form" method="POST" action="{{ route('profile.requestEdit') }}" class="hidden">
                    @csrf
                </form>
                <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6 lg:items-center">
                    <div class="max-w-1xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                   <div class="flex flex-col items-end gap-16 lg:self-start p-4">
                        <button type="submit" form="profile-request-form" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60"
                            @disabled(($editRequest?->status ?? null) === 'pending' || ($editRequest?->status ?? null) === 'approved')>
                            @if(($editRequest?->status ?? null) === 'approved')
                                Request Approved
                            @elseif(($editRequest?->status ?? null) === 'pending')
                                Request Pending
                            @else
                                Edit Profile Request
                            @endif
                        </button>
                        

                        <div x-data="{
                                preview: @js($avatar ?? asset('images/avatar.jpg')),
                                stream: null,
                                streaming: false,
                                setPreview(file) {
                                    if (!file) { return; }
                                    const reader = new FileReader();
                                    reader.onload = e => { this.preview = e.target?.result; };
                                    reader.readAsDataURL(file);
                                },
                                clear() {
                                    const input = this.$refs.uploadInput;
                                    if (input) { input.value = ''; }
                                    this.preview = @js($avatar ?? asset('images/avatar.jpg'));
                                    this.stopCamera();
                                },
                                async startCamera() {
                                    if (!this.editable) return;
                                    try {
                                        this.stopCamera();
                                        const stream = await navigator.mediaDevices?.getUserMedia?.({ video: true });
                                        if (!stream) return;
                                        this.stream = stream;
                                        this.streaming = true;
                                        const video = this.$refs.video;
                                        if (video) {
                                            video.srcObject = stream;
                                            await video.play();
                                        }
                                    } catch (e) {
                                        console.error(e);
                                        this.streaming = false;
                                    }
                                },
                                captureFrame() {
                                    if (!this.streaming || !this.editable) return;
                                    const video = this.$refs.video;
                                    const canvas = this.$refs.canvas;
                                    if (!video || !canvas) return;
                                    const { videoWidth: w, videoHeight: h } = video;
                                    if (!w || !h) return;
                                    canvas.width = w;
                                    canvas.height = h;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(video, 0, 0, w, h);
                                    canvas.toBlob(blob => {
                                        if (!blob) return;
                                        const file = new File([blob], 'profile_photo.jpg', { type: 'image/jpeg' });
                                        const dt = new DataTransfer();
                                        dt.items.add(file);
                                        this.$refs.uploadInput.files = dt.files;
                                        this.setPreview(file);
                                        this.stopCamera();
                                    }, 'image/jpeg', 0.9);
                                },
                                stopCamera() {
                                    if (this.stream) {
                                        this.stream.getTracks().forEach(t => t.stop());
                                    }
                                    this.stream = null;
                                    this.streaming = false;
                                },
                                chooseUpload() {
                                    if (!this.editable) return;
                                    this.stopCamera();
                                    this.$refs.uploadInput?.click();
                                }
                            }" x-effect="if(!editable) { stopCamera(); clear(); }" class="flex flex-col items-center text-center space-y-3 w-full">
                            <div class="relative w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 lg:w-64 lg:h-64 rounded-full overflow-hidden border border-gray-200 shadow-sm bg-white">
                                <video x-ref="video" class="absolute inset-0 h-full w-full object-cover" x-show="streaming" playsinline muted></video>
                                <template x-if="preview && !streaming">
                                    <img :src="preview" alt="Profile photo" class="w-full h-full object-cover" />
                                </template>
                                <template x-if="!preview && !streaming">
                                    <div class="flex h-full w-full items-center justify-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </div>
                                </template>
                            </div>

                            <div class="grid grid-cols-2 gap-3 w-full max-w-md" x-show="editable" x-cloak>
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-400 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300 w-full" @click="startCamera()" x-show="!streaming">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 8h.01"/><path d="M17 6h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2"/><path d="m3 10 2.586-2.586a2 2 0 0 1 2.828 0L12 11l2.586-2.586a2 2 0 0 1 2.828 0L21 11"/><circle cx="12" cy="13" r="3"/></svg>
                                    Camera 
                                </button>

                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-400 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300 w-full" @click="chooseUpload()" x-show="!streaming">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m19 12-7-7-7 7"/><path d="M5 19h14"/></svg>
                                    Upload
                                </button>
                                
                            </div>

                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:border-emerald-500 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300" @click="captureFrame()" x-show="streaming && editable" x-cloak>
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
                                Capture
                            </button>

                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-transparent px-4 py-2 text-xs font-semibold text-slate-500 transition hover:text-rose-600" @click="clear()" x-show="(preview || streaming) && editable" x-cloak>
                                Remove photo
                            </button>

                            <input x-ref="uploadInput" type="file" name="profile_photo" form="profile-update-form" accept="image/*" class="hidden" @change="setPreview($event.target.files[0])" :disabled="!editable">
                            <canvas x-ref="canvas" class="hidden"></canvas>

                        </div>
                    </div>

                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
    </div>
    @endif
</x-app-layout>
