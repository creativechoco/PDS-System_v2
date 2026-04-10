<div
    {{ $attributes->merge(['class' => 'fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-2 sm:px-6 py-3 sm:py-4']) }}
    x-cloak
    @keydown.escape.window="$dispatch('close')"
    @click.self="$dispatch('close')"
>
    <div
        class="
            bg-white
            w-full
            h-full
            max-w-[95vw]
            max-h-[95vh]
            sm:max-w-10xl
            sm:max-h-[90vh]
            rounded-xl
            sm:rounded-2xl
            shadow-2xl
            overflow-hidden
            flex
            flex-col
        "
        x-data="{ fullPreview: false }"
    >

        <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100">
            <div>
                <h2 class="uppercase font-semibold text-indigo-500 text-sm sm:text-base">PDS Submission</h2>
            </div>
            <button type="button" class="rounded-full p-1.5 sm:p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100" @click="$dispatch('close')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
                </svg>
            </button>
        </div>
        
    <div class="flex-1 overflow-hidden">
        <div class="grid gap-5 sm:gap-6 px-4 sm:px-6 py-5 sm:py-6 h-full lg:grid-cols-[280px_minmax(0,1fr)] items-start">
            <div class="flex flex-col h-full pr-1 sm:pr-2 lg:pr-0 gap-4">
                <div class="space-y-4 sm:space-y-5 flex-1">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <img :src="selected?.avatar" :alt="(selected?.name ?? 'User') + ' avatar'" class="h-10 w-10 sm:h-12 sm:w-12 rounded-full object-cover shadow" />
                        <div class="min-w-0">
                            <p class="text-sm sm:text-base font-semibold text-slate-900 truncate" x-text="selected?.name ?? '—'"></p>
                            <p class="text-xs sm:text-sm text-slate-500 truncate" x-text="selected?.type ?? '—'"></p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:gap-5 text-xs sm:text-sm text-slate-700">
                        <div>
                            <p class="text-[11px] sm:text-xs uppercase text-slate-400 font-semibold">Email</p>
                            <p class="font-medium break-words" x-text="selected?.email ?? '—'"></p>
                        </div>
                        <div>
                            <p class="text-[11px] sm:text-xs uppercase text-slate-400 font-semibold">Division/Section/Unit/Office</p>  
                            <p class="font-medium break-words" x-text="selected?.unit ?? '—'"></p>
                        </div>
                        <div class="flex flex-col gap-2 sm:gap-2.5">
                            <p class="text-[11px] sm:text-xs uppercase text-slate-400 font-semibold">Status</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button"
                                    class="rounded-full px-3 py-1.5 text-[11px] sm:text-xs font-semibold border"
                                    :class="(selected?.status_key ?? 'pending') === 'pending'
                                        ? 'border-amber-200 bg-amber-50 text-amber-700 shadow-sm'
                                        : 'border-slate-200 text-slate-600 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700'"
                                    @click.stop="requestConfirm('pending')">
                                    Pending
                                </button>
                                <button type="button"
                                    class="rounded-full px-3 py-1.5 text-[11px] sm:text-xs font-semibold border"
                                    :class="(selected?.status_key ?? 'pending') === 'approved'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 shadow-sm'
                                        : 'border-slate-200 text-slate-600 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700'"
                                    @click.stop="requestConfirm('approved')">
                                    Approve
                                </button>
                                <button type="button"
                                    class="rounded-full px-3 py-1.5 text-[11px] sm:text-xs font-semibold border"
                                    :class="(selected?.status_key ?? 'pending') === 'rejected'
                                        ? 'border-rose-200 bg-rose-50 text-rose-700 shadow-sm'
                                        : 'border-slate-200 text-slate-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700'"
                                    @click.stop="requestConfirm('rejected')">
                                    Reject
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-[11px] sm:text-xs uppercase text-slate-400 font-semibold">Submitted</p>
                            <p class="font-medium" x-text="selected?.submitted_at ?? '—'"></p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2.5 sm:gap-3 pb-4 sm:pb-5 mt-auto justify-end">
                    <button type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-xs sm:text-sm font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100"
                        @click.stop="downloadPds()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M12 3v12" />
                            <path d="m8 11 4 4 4-4" />
                            <path d="M4 19h16" />
                        </svg>
                        Download PDS
                    </button>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl border border-slate-100 overflow-hidden flex flex-col min-h-0 h-full relative">
                <div class="flex items-center justify-between px-3 sm:px-4 py-2.5 sm:py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700">Preview</p>
                </div>
                <div class="flex-1 overflow-auto bg-white">
                    <iframe
                        class="h-full min-h-[260px] sm:min-h-[360px] lg:min-h-[500px] w-full min-w-[900px] sm:min-w-[1024px] max-w-none bg-white"
                        x-show="selected?.user_id"
                        :src="selected?.user_id ? `/pds-preview/${selected.user_id}?ts=${Date.now()}` : ''"
                        frameborder="0"
                        allowfullscreen
                    ></iframe>
                </div>
                <button type="button" class="absolute bottom-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 backdrop-blur border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 shadow hover:border-indigo-200 hover:text-indigo-600 "
                    x-show="selected?.user_id"
                    @click.stop="fullPreview = true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5H5.75A1.25 1.25 0 0 0 4.5 5.75v2.5m0 7.5v2.5a1.25 1.25 0 0 0 1.25 1.25h2.5m7.5-15h2.5A1.25 1.25 0 0 1 19.5 5.75v2.5m0 7.5v2.5a1.25 1.25 0 0 1-1.25 1.25h-2.5" />
                    </svg>
                    Full screen
                </button>
            </div>
        </div>
    </div>

    <!-- Full-screen preview overlay -->
    <div x-show="fullPreview" x-transition.opacity x-cloak class="fixed inset-0 z-70 bg-slate-900/70 flex items-center justify-center px-3 sm:px-6 py-4 "
        @keydown.escape.window="fullPreview = false" @click.self="fullPreview = false">
        <div class="bg-white w-full h-full max-w-6xl max-h-[95vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100">
                <p class="text-sm sm:text-base font-semibold text-slate-800">PDS Preview</p>
                <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100" @click="fullPreview = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto bg-white">
                <iframe
                    class="w-full min-w-[1024px] max-w-none h-full bg-white"
                    x-show="selected?.user_id"
                    :src="selected?.user_id ? `/pds-preview/${selected.user_id}?ts=${Date.now()}` : ''"
                    frameborder="0"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <template x-if="confirmOpen">
        <div class="fixed inset-0 z-60 flex items-center justify-center bg-slate-900/40 px-4 py-6" x-cloak @keydown.escape.window="cancelConfirm()" @click.self="cancelConfirm()">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center" :class="confirmAction === 'approved' ? 'bg-emerald-400' : confirmAction === 'rejected' ? 'bg-rose-400' : 'bg-amber-400'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#edf1edff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-check-icon lucide-file-check"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="m9 15 2 2 4-4"/></svg>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-slate-900"
                                x-text="confirmAction === 'approved' ? 'Approve submission?' : confirmAction === 'rejected' ? 'Reject submission?' : 'Mark as pending?'"></p>
                        </div>
                    </div>
                    <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100" @click="cancelConfirm()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 flex flex-col gap-4">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <p class="text-md text-slate-700" x-text="confirmAction === 'approved' ? 'This will approve the submitted PDS.' : confirmAction === 'rejected' ? 'This will reject the submitted PDS.' : 'This will mark the submitted PDS as pending.'"></p>
                    </div>
                    <template x-if="confirmAction === 'rejected'">
                        <div class="flex flex-col gap-3">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Incorrect sections <span class="text-xs font-normal text-slate-400">(click to toggle highlight)</span></label>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-for="sec in [
                                        {key:'personal_information',   label:'I. Personal Information'},
                                        {key:'family_background',      label:'II. Family Background'},
                                        {key:'educational_background', label:'III. Educational Background'},
                                        {key:'civil_service',          label:'IV. Civil Service Eligibility'},
                                        {key:'work_experience',        label:'V. Work Experience'},
                                        {key:'voluntary_work',         label:'VI. Voluntary Work'},
                                        {key:'learning_development',   label:'VII. L&D / Training'},
                                        {key:'other_information',      label:'VIII. Other Information'},
                                        {key:'references',             label:'References'},
                                        {key:'work_experience_sheet',  label:'Work Experience Sheet'}
                                    ]" :key="sec.key">
                                        <button type="button"
                                            class="rounded-full border px-3 py-1 text-xs font-semibold transition-colors"
                                            :class="rejectSections.includes(sec.key)
                                                ? 'border-rose-500 bg-rose-500 text-white'
                                                : 'border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100'"
                                            @click="rejectSections.includes(sec.key)
                                                ? rejectSections = rejectSections.filter(s => s !== sec.key)
                                                : rejectSections.push(sec.key)"
                                            x-text="sec.label">
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-700">Rejection note <span class="text-xs font-normal text-slate-400">(optional)</span></label>
                                <textarea class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-rose-500 focus:ring-rose-200"
                                    rows="3" placeholder="Add a short reason for rejection"
                                    x-model.trim="rejectNote"></textarea>
                            </div>
                        </div>
                    </template>
                    <div class="flex gap-3">
                        <button type="button" class="flex-1 rounded-xl border px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300" @click="cancelConfirm()">Cancel</button>
                        <button type="button" class="flex-1 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm"
                            :class="confirmAction === 'approved' ? 'bg-emerald-600 hover:bg-emerald-500' : confirmAction === 'rejected' ? 'bg-rose-600 hover:bg-rose-500' : 'bg-amber-600 hover:bg-amber-500'"
                            @click="confirmStatus()"
                            x-text="confirmAction === 'approved' ? 'Confirm' : confirmAction === 'rejected' ? 'Reject' : 'Mark Pending'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
