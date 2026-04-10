<x-app-layout>

    <div class="py-5 md:py-8 lg:py-10">
        <div class="mx-auto px-2 sm:px-6 md:px-12 lg:px-20 space-y-8 flex flex-col h-[calc(100vh-120px)] sm:h-[calc(100vh-150px)] lg:h-[calc(100vh-180px)]">

            {{-- Header --}}
            <div class="flex flex-row flex-nowrap items-center justify-between gap-2 sm:gap-3 lg:gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm uppercase tracking-wide text-indigo-500 font-semibold">PDS Review</p>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 leading-tight">PDS Submissions</h1>
                    <p class="text-slate-500 text-xs sm:text-sm lg:text-base">
                        Monitor employees who submitted their Personal Data Sheets.
                    </p>
                </div>
                <div class="flex items-center gap-2 lg:hidden flex-shrink-0">
                    <a href="{{ route('pds.export') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white shadow-sm hover:bg-indigo-500" title="Export Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    </a>
                </div>
                <div class="hidden lg:flex flex-row gap-3 flex-shrink-0">
                    <a href="{{ route('pds.export') }}"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                        Export Excel
                    </a>
                </div>
            </div>

            {{-- Table --}}
            <div
                class="bg-white shadow-sm sm:rounded-2xl border border-slate-100 flex flex-col flex-1 min-h-0"
                x-data="{
                    search: '',
                    @if(request('status') === 'pending')
                    activeTab: 'pending',
                    @elseif(request('status') === 'approved')
                    activeTab: 'approved',
                    @elseif(request('status') === 'rejected')
                    activeTab: 'rejected',
                    @else
                    activeTab: 'all',
                    @endif
                    filtersOpen: false,
                    searchOpen: false,
                    modalOpen: false,
                    selected: null,
                    confirmOpen: false,
                    confirmAction: null,
                    rejectNote: '',
                    rejectSections: [],

                    submissions: {{ Js::from($submissions ?? []) }},

                    init() {
                        window.addEventListener('pds-submissions-update', (event) => {
                            const next = event.detail?.submissions;
                            if (Array.isArray(next)) {
                                if (this.modalOpen) {
                                    const map = new Map(this.submissions.map((s) => [s.id, s]));
                                    next.forEach((n) => {
                                        const existing = map.get(n.id);
                                        if (existing) {
                                            Object.assign(existing, n);
                                        }
                                    });

                                    if (this.selected) {
                                        const updated = map.get(this.selected.id);
                                        if (updated) {
                                            this.selected = updated;
                                        }
                                    }
                                } else {
                                    this.submissions = next;
                                }
                            }
                        });

                        // Auto-open preview when highlight query param is present (from notification)
                        const params = new URLSearchParams(window.location.search);
                        const highlight = params.get('highlight');
                        if (highlight) {
                            // match by user_id first, else by submission id
                            const match = this.submissions.find(
                                (s) => String(s.user_id) === String(highlight) || String(s.id) === String(highlight)
                            );
                            if (match) {
                                this.open(match);
                            }
                        }
                    },

                    normalized(v) {
                        return (v ?? '').toString().trim().toLowerCase();
                    },

                    tabCount(tab) {
                        if (tab === 'all') return this.submissions.length;
                        return this.submissions.filter((s) => this.normalized(s.status_key ?? s.status) === tab).length;
                    },

                    filtered() {
                        const q = this.normalized(this.search);
                        const tab = this.normalized(this.activeTab);

                        return this.submissions
                            .map((s) => {
                                const statusKey = this.normalized(s.status_key ?? s.status);
                                return {
                                    ...s,
                                    status_key: ['approved', 'pending', 'rejected'].includes(statusKey) ? statusKey : 'pending',
                                };
                            })
                            .filter((s) => {
                                if (tab !== 'all' && s.status_key !== tab) return false;
                                if (!q) return true;

                                return (
                                    this.normalized(s.name).includes(q) ||
                                    this.normalized(s.email).includes(q) ||
                                    this.normalized(s.unit).includes(q)
                                );
                            });
                    },

                    open(submission) {
                        this.selected = submission;
                        this.modalOpen = true;
                    },

                    requestConfirm(newStatus) {
                        this.confirmAction = newStatus;
                        if (newStatus !== 'rejected') {
                            this.rejectNote = '';
                            this.rejectSections = [];
                        }
                        this.confirmOpen = true;
                    },

                    confirmStatus() {
                        if (!this.confirmAction) return;
                        this.setStatus(
                            this.confirmAction,
                            this.confirmAction === 'rejected' ? this.rejectNote : '',
                            this.confirmAction === 'rejected' ? this.rejectSections : []
                        );
                        this.confirmAction = null;
                        this.rejectNote = '';
                        this.rejectSections = [];
                        this.confirmOpen = false;
                    },

                    cancelConfirm() {
                        this.confirmAction = null;
                        this.rejectNote = '';
                        this.rejectSections = [];
                        this.confirmOpen = false;
                    },

                    async setStatus(newStatus, note = '', highlightedSections = []) {
                        if (!this.selected) return;
                        const statusKey = this.normalized(newStatus);
                        const statusLabel = statusKey === 'approved'
                            ? 'Approved'
                            : statusKey === 'rejected'
                                ? 'Rejected'
                                : 'Pending';

                        try {
                            const response = await fetch(`/pds-form/${this.selected.id}/status`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                },
                                body: JSON.stringify({ status: statusLabel, note, highlighted_sections: highlightedSections }),
                            });

                            if (!response.ok) throw new Error('Failed to update status');

                            const updated = { ...this.selected, status_key: statusKey, status: statusLabel };
                            this.selected = updated;

                            this.submissions = this.submissions.map((s) =>
                                s.id === updated.id
                                    ? { ...s, status_key: statusKey, status: statusLabel }
                                    : s
                            );
                        } catch (error) {
                            console.error('Error updating status:', error);
                            alert('Failed to update status. Please try again.');
                        }
                    },

                    downloadPds() {
                        if (!this.selected?.user_id) return;
                        const userId = encodeURIComponent(this.selected.user_id);
                        window.open(`/pds-preview/${userId}/download?ts=${Date.now()}`, '_blank');
                    },

                    close() {
                        this.modalOpen = false;
                        this.selected = null;
                    },
                }"
            >

                {{-- Tabs + Search --}}
                <div
                    class="px-4 sm:px-6 lg:px-8 py-3 sm:py-4 flex flex-col gap-2.5 sm:gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-slate-100">

                    <!-- Mobile toggle row -->
                    <div class="flex items-center justify-between lg:hidden">
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                            @click="filtersOpen = !filtersOpen"
                            :aria-expanded="filtersOpen">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h14M3 12h18M3 18h10" />
                            </svg>
                            Status
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                            x-on:click="searchOpen = !searchOpen" :aria-expanded="searchOpen">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                            Search
                        </button>
                    </div>

                    <!-- Mobile filters panel -->
                    <div x-show="filtersOpen" x-transition class="lg:hidden rounded-2xl border border-slate-100 bg-slate-50 px-3.5 py-3 space-y-2 shadow-sm">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" class="rounded-full border px-3 py-2 text-xs sm:text-sm font-semibold"
                                :class="activeTab === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                @click="activeTab = 'all'">All</button>
                            <button type="button" class="rounded-full border px-3 py-2 text-xs sm:text-sm font-semibold"
                                :class="activeTab === 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                @click="activeTab = 'pending'">Pending</button>
                            <button type="button" class="rounded-full border px-3 py-2 text-xs sm:text-sm font-semibold"
                                :class="activeTab === 'approved' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                @click="activeTab = 'approved'">Approved</button>
                            <button type="button" class="rounded-full border px-3 py-2 text-xs sm:text-sm font-semibold"
                                :class="activeTab === 'rejected' ? 'bg-rose-600 text-white border-rose-600 shadow-sm' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                @click="activeTab = 'rejected'">Rejected</button>
                        </div>
                         
                    </div>

                    <!-- Tabs desktop -->
                    <div class="hidden lg:flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
                            :class="activeTab === 'all'
                                ? 'bg-slate-900 text-white border-slate-900 shadow-sm'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            @click="activeTab = 'all'">
                            All
                            <span class="ms-1 text-xs font-medium" x-text="'(' + tabCount('all') + ')'" ></span>
                        </button>

                        <button
                            type="button"
                            class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
                            :class="activeTab === 'pending'
                                ? 'bg-amber-500 text-white border-amber-500 shadow-sm'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            @click="activeTab = 'pending'">
                            Pending
                            <span class="ms-1 text-xs font-medium" x-text="'(' + tabCount('pending') + ')'" ></span>
                        </button>

                        <button
                            type="button"
                            class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
                            :class="activeTab === 'approved'
                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            @click="activeTab = 'approved'">
                            Approved
                            <span class="ms-1 text-xs font-medium" x-text="'(' + tabCount('approved') + ')'" ></span>
                        </button>

                        <button
                            type="button"
                            class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
                            :class="activeTab === 'rejected'
                                ? 'bg-rose-600 text-white border-rose-600 shadow-sm'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            @click="activeTab = 'rejected'">
                            Rejected
                            <span class="ms-1 text-xs font-medium" x-text="'(' + tabCount('rejected') + ')'" ></span>
                        </button>
                    </div>

                    {{-- Desktop search --}}
                    <div class="hidden lg:block relative w-full lg:w-64">
                        <input
                            type="text"
                            placeholder="Search submission"
                            class="w-full rounded-2xl border border-slate-200 py-2 ps-9 pe-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            x-model.debounce.200ms="search"
                            @keydown.escape="search = ''"
                        />
                        <span class="absolute left-3 top-2.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                        </span>
                    </div>

                    <!-- Mobile search panel -->
                    <div x-show="searchOpen" x-transition class="lg:hidden relative w-full">
                        <input
                            type="text"
                            placeholder="Search submission"
                            class="w-full rounded-2xl border border-slate-200 py-2 ps-9 pe-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            x-model.debounce.200ms="search"
                            @keydown.escape="search = ''"
                        />
                        <span class="absolute left-3 top-2.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- Table --}}
                <div class="flex-1 overflow-hidden">
                    <div class="overflow-x-auto lg:overflow-visible h-full">
                        <div class="max-h-full min-h-full overflow-y-auto rounded-b-2xl bg-white">
                            <table class="w-full divide-y divide-slate-100">
                                <thead
                                    class="sticky top-0 z-10 bg-slate-50 backdrop-blur text-left text-xs sm:text-sm font-semibold uppercase text-slate-500 shadow-[0_6px_12px_-12px_rgba(15,23,42,0.35)]">
                                    <tr>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Employee</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Division/Section/Unit/Office</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Email</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Submitted</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Status</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3 text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white text-xs sm:text-sm text-slate-700">

                                    <template x-if="filtered().length === 0">
                                        <tr>
                                            <td colspan="6"
                                                class="px-6 py-8 text-center text-slate-500">
                                                No submissions yet.
                                            </td>
                                        </tr>
                                    </template>

                                    <template x-for="(submission, idx) in filtered()" :key="idx">
                                        <tr
                                            class="hover:bg-slate-50"
                                            x-data="{
                                                statusClass() {
                                                    if (submission.status_key === 'approved')
                                                        return 'text-emerald-600 bg-emerald-50';
                                                    if (submission.status_key === 'rejected')
                                                        return 'text-rose-600 bg-rose-50';
                                                    return 'text-amber-600 bg-amber-50';
                                                }
                                            }"
                                            x-cloak
                                        >
                                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                                <div class="flex items-center gap-3">
                                                    <img :src="submission.avatar" :alt="submission.name + ' avatar'" class="h-9 w-9 sm:h-10 sm:w-10 rounded-full object-cover shadow-sm" />
                                                    <div>
                                                        <p class="font-semibold text-slate-900 text-sm sm:text-base truncate max-w-[14ch] sm:max-w-none" x-text="submission.name"></p>
                                                        <span class="text-slate-500 text-[11px] sm:text-xs" x-text="submission.type ?? '—'"></span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-normal break-words min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]" x-text="submission.unit"></td>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500 whitespace-normal break-words min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]" x-text="submission.email"></td>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4" x-text="submission.submitted_at ?? '—'"></td>

                                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] sm:text-xs font-semibold"
                                                    :class="statusClass()"
                                                    x-text="submission.status">
                                                </span>
                                            </td>

                                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-center">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-full border border-indigo-200 px-3.5 sm:px-4 py-1.5 text-[11px] sm:text-xs font-semibold text-indigo-600 hover:bg-indigo-50"
                                                    @click.prevent="open(submission)"
                                                >
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Modal Component inside scope for selected/modalOpen --}}
                <x-pds-preview x-show="modalOpen" @close="close" />

            </div>

        </div>
    </div>

</x-app-layout>
