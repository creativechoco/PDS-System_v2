<x-app-layout>

    <div class="py-5 md:py-8 lg:py-10"
        x-data="{
            addEmployeeOpen: false,
            confirmOpen: false,
            confirmType: '',
            confirmTitle: '',
            confirmBody: '',
            archiveConfirmOpen: false,
            archiveTarget: null,
            archiveError: '',
            archiving: false,

            viewUserId: {{ request('view_user') ? (int) request('view_user') : 'null' }},

            newEmployeeName: '',
            newEmployeeUnit: '',
            savingEmployee: false,
            employeeError: '',
            employeeFieldErrors: {},
            init() {
                if (this.viewUserId) {
                    // wait a tick for modals to be registered
                    setTimeout(() => this.openUserModalById(this.viewUserId), 50);
                }
            },
            openUserModalById(id) {
                if (!id) return;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'employee-details-' + id }));
            },
            openEmployee() {
                this.employeeError = '';
                this.employeeFieldErrors = {};
                this.newEmployeeName = '';
                this.newEmployeeUnit = '';
                this.savingEmployee = false;
                this.addEmployeeOpen = true;
            },
            closeEmployee() {
                this.addEmployeeOpen = false;
            },
            closeConfirm() {
                this.confirmOpen = false;
                this.confirmType = '';
                this.confirmTitle = '';
                this.confirmBody = '';
            },
            requestConfirm(type) {
                if (this.savingEmployee) return;
                this.confirmTitle = 'Add New Employee';
                this.confirmBody = 'You are about to add ' + this.newEmployeeName.toUpperCase() + ' to the directory. Are you sure?';
                this.confirmType = type;
                this.confirmOpen = true;
            },
            confirmSubmit() {
                if (this.confirmType === 'employee') {
                    this.closeConfirm();
                    this.submitEmployee();
                }
            },
            requestArchive(employee) {
                console.log('requestArchive called for:', employee);
                this.archiveTarget = employee;
                this.archiveError = '';
                this.archiving = false;
                this.archiveConfirmOpen = true;
            },
            closeArchiveConfirm() {
                this.archiveConfirmOpen = false;
                this.archiveTarget = null;
                this.archiveError = '';
                this.archiving = false;
            },
            async performArchive() {
                if (!this.archiveTarget || this.archiving) return;
                this.archiving = true;
                this.archiveError = '';

                console.log('performArchive called for:', this.archiveTarget);

                try {
                    const res = await fetch('/manage-user/' + this.archiveTarget.id + '/archive', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                        }
                    });
                    console.log('Response status:', res.status);
                    const data = await res.json().catch(() => null);
                    console.log('Response data:', data);
                    if (!res.ok) {
                        throw new Error(data?.message || 'Failed to archive employee.');
                    }
                    window.dispatchEvent(new CustomEvent('employee-archived', { detail: { id: this.archiveTarget.id, email: this.archiveTarget.email } }));
                    this.closeArchiveConfirm();
                } catch (err) {
                    console.error('Archive error:', err);
                    this.archiveError = err.message || 'Failed to archive employee.';
                } finally {
                    this.archiving = false;
                }
            },
            submitEmployee() {
                if (this.savingEmployee) return;
                this.employeeError = '';
                this.employeeFieldErrors = {};
                this.savingEmployee = true;

                fetch('{{ route('registration-users.store', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        full_name: this.newEmployeeName,
                        unit: this.newEmployeeUnit,
                    })
                })
                .then(async (res) => {
                    if (res.redirected || res.status === 302) {
                        throw new Error('Session expired or unauthorized. Please log in as an admin.');
                    }

                    const data = await res.json().catch(() => null);
                    const errors = data?.errors || {};

                    if (res.status !== 201) {
                        this.employeeFieldErrors = errors;

                        let msg = data?.message;
                        if (!msg && res.status === 422) {
                            const combined = Object.values(errors).flat().join(' ') || '';
                            if (combined.toLowerCase().includes('already been taken')) {
                                msg = 'Name already exists.';
                            } else {
                                msg = 'Please fix the highlighted errors.';
                            }
                        }
                        if (!msg && (res.status === 401 || res.status === 403)) {
                            msg = 'You are not authorized to add employees.';
                        }
                        if (!msg) {
                            msg = res.status >= 500 ? 'Server error. Please try again.' : 'Unable to add employee. Please try again.';
                        }

                        throw new Error(msg);
                    }

                    this.addEmployeeOpen = false;
                    this.newEmployeeName = '';
                    this.newEmployeeUnit = '';
                    this.employeeFieldErrors = {};
                })
                .catch(err => {
                    this.employeeError = err.message || 'Unable to add employee.';
                })
                .finally(() => {
                    this.savingEmployee = false;
                });
            }
        }"
        x-init="init()"
        x-cloak>
        

        <div class="mx-auto px-2 sm:px-6 md:px-12 lg:px-20 space-y-8 flex flex-col h-[calc(100vh-120px)] sm:h-[calc(100vh-150px)] lg:h-[calc(100vh-180px)]">

            <div class="flex flex-row flex-nowrap items-center justify-between gap-2 sm:gap-3 lg:gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm uppercase tracking-wide text-indigo-500 font-semibold">Team Directory</p>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 leading-tight">Manage Employees</h1>
                    <p class="text-slate-500 text-xs sm:text-sm lg:text-base">Review account status, employment status, and contact details in one place.</p>
                </div>
                <!-- Icon buttons for mobile/tablet -->
                <div class="flex items-center gap-2 lg:hidden flex-shrink-0">
                    <button type="button"
                        @click="openEmployee()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-indigo-200 bg-white text-indigo-600 shadow-sm hover:bg-indigo-50"
                        title="Add Employee">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 item-center"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-plus-icon lucide-user-round-plus"><path d="M2 21a8 8 0 0 1 13.292-6"/><circle cx="10" cy="8" r="5"/><path d="M19 16v6"/><path d="M22 19h-6"/></svg>
                    </button>
                    <a href="{{ route('manage-user.export') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white shadow-sm hover:bg-indigo-500" title="Export Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    </a>
                </div>

                <!-- Full buttons for desktop -->
                <div class="hidden lg:flex flex-row gap-3">
                    <button type="button"
                        @click="openEmployee()"
                        class="inline-flex items-center justify-center rounded-xl border border-indigo-200 px-4 py-2 text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50 shadow-sm">
                        Add Employee
                    </button>

                    <a href="{{ route('manage-user.export') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                        Export Excel
                    </a>
                </div>
            </div>

            <!-- Search functionality, add if needed-->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100 flex flex-col flex-1 min-h-0" 
                x-data="{ 
                    search: '',
                    filterStatus: '',
                    @if(request('status') === 'permanent')
                    filterType: 'Permanent Employee',
                    @elseif(request('status') === 'contract')
                    filterType: 'Contract of Service',
                    @elseif(request('status') === 'joborder')
                    filterType: 'Job Order',
                    @else
                    filterType: '',
                    @endif
                    sortKey: 'created_at',
                    sortDir: 'desc',
                    filtersOpen: false,
                    searchOpen: false,
                    employees: @js($employees),
                    units: @js($units ?? []),
                    init() {
                        window.addEventListener('employee-archived', (e) => {
                            const id = e.detail?.id;
                            const email = e.detail?.email;
                            if (id !== undefined && id !== null) {
                                this.removeEmployeeById(id);
                            } else if (email) {
                                this.removeEmployeeByEmail?.(email);
                            }
                        });
                        window.addEventListener('employee-updated', (e) => {
                            const user = e.detail?.user;
                            if (user && user.is_archive) {
                                // User was automatically archived due to status change
                                window.dispatchEvent(new CustomEvent('employee-archived', { detail: { id: user.id, email: user.email } }));
                            } else if (user && user.status === 'Active' && !user.is_archive) {
                                // User was automatically unarchived due to status change
                                // This case is handled by the normal update flow
                            }
                        });
                    },
                    filteredSorted() {
                        const norm = (v) => (v ?? '').toString().trim().toLowerCase();

                        return this.employees
                            .filter(e => {
                                const q = this.search.toLowerCase();
                                const matchesSearch = !q || norm(e.name).includes(q) || norm(e.phone).includes(q) || norm(e.email).includes(q);

                                const statusFilter = norm(this.filterStatus);
                                const matchesStatus = !statusFilter || norm(e.status) === statusFilter;

                                const typeFilter = norm(this.filterType);
                                const matchesType = !typeFilter || norm(e.type) === typeFilter;
                                return matchesSearch && matchesStatus && matchesType;
                            })
                            .sort((a, b) => {
                                const dir = this.sortDir === 'asc' ? 1 : -1;
                                const key = ['name', 'unit', 'email', 'phone', 'created_at'].includes(this.sortKey) ? this.sortKey : 'name';

                                const as = a[key];
                                const bs = b[key];

                                // Date sort for created_at when available
                                if (key === 'created_at') {
                                    const at = Number(new Date(as));
                                    const bt = Number(new Date(bs));
                                    if (Number.isFinite(at) && Number.isFinite(bt) && at !== bt) {
                                        return (at - bt) * dir;
                                    }
                                }

                                const av = norm(as);
                                const bv = norm(bs);
                                const primary = av.localeCompare(bv);
                                if (primary !== 0) return primary * dir;

                                // Secondary tiebreak by name for stability
                                return norm(a.name).localeCompare(norm(b.name)) * dir;
                            });
                    },
                    clearFilters() {
                        this.search = '';
                        this.filterStatus = '';
                        this.filterType = '';
                        this.sortKey = 'created_at';
                        this.sortDir = 'desc';
                    },
                    removeEmployeeById(id) {
                        const targetId = Number(id);
                        this.employees = this.employees.filter(e => Number(e.id) !== targetId);
                    },
                    removeEmployeeByEmail(email) {
                        const norm = (v) => (v ?? '').toString().trim().toLowerCase();
                        this.employees = this.employees.filter(e => norm(e.email) !== norm(email));
                    }
                }"
                x-init="init()">
                <div class="px-4 sm:px-6 lg:px-8 py-3 sm:py-4 flex flex-col gap-2.5 sm:gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-slate-100">
                    <!-- Mobile controls toggle row -->
                    <div class="flex items-center justify-between lg:hidden">
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                            @click="filtersOpen = !filtersOpen"
                            :aria-expanded="filtersOpen">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h14M3 12h18M3 18h10" />
                            </svg>
                            Filters 
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                            @click="searchOpen = !searchOpen"
                            :aria-expanded="searchOpen">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                            Search
                        </button>
                    </div>

                    <!-- Desktop filters -->
                    <div class="hidden lg:flex flex-wrap items-center gap-2">
                        <select class="rounded-full border border-slate-200/90 bg-white px-4.5 py-2 text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="filterStatus">
                            <option value="">Status: All</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <select class="rounded-full border border-slate-200/90 bg-white px-4.5 py-2 text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500" x-model="filterType">
                            <option value="">Employment Status: All</option>
                            <option value="Permanent Employee">Permanent</option>
                            <option value="Contract of Service">Contract of Service</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                        <select class="rounded-full border border-slate-200/90 bg-white px-4.5 py-2 text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="sortKey">
                            <option value="created_at">Sort: Date</option>
                            <option value="name">Sort: Name</option>
                            <option value="email">Sort: Email</option>
                            <option value="phone">Sort: Phone</option>
                        </select>
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                            x-on:click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8 9 4-4 4 4m0 6-4 4-4-4" />
                            </svg>
                            <span x-text="sortDir === 'asc' ? 'Ascending' : 'Descending'"></span>
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-rose-100 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-600 hover:border-rose-200 hover:bg-rose-100"
                            x-on:click="clearFilters()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m4 4 16 16" />
                                <path d="M10 4h10v2a2 2 0 0 1-2 2h-2" />
                                <path d="m8 8-4 4v2a2 2 0 0 0 2 2h6" />
                            </svg>
                            Reset
                        </button>
                    </div>

                    <!-- Desktop search -->
                    <div class="hidden lg:block relative w-full lg:w-72">
                        <input type="text" placeholder="Search employee" class="w-full rounded-2xl border border-slate-200/90 bg-white py-2.5 ps-10 pe-3 text-sm font-medium text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            x-model.debounce.200ms="search" x-on:keydown.escape="search = ''" />
                        <span class="absolute left-3 top-2.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                        </span>
                    </div>

                    <!-- Mobile filters panel -->
                    <div x-show="filtersOpen" x-transition class="lg:hidden rounded-2xl border border-slate-100 bg-slate-50 px-3.5 py-3 space-y-2 shadow-sm">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <select class="rounded-full border border-slate-200/90 bg-white px-3.5 sm:px-4.5 py-2 text-xs sm:text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="filterStatus">
                                <option value="">Status: All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            <select class="rounded-full border border-slate-200/90 bg-white px-3.5 sm:px-4.5 py-2 text-xs sm:text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500" x-model="filterType">
                                <option value="">Employment Status: All</option>
                                <option value="Permanent Employee">Permanent</option>
                                <option value="Contract of Service">Contract of Service</option>
                                <option value="Job Order">Job Order</option>
                            </select>
                            <select class="rounded-full border border-slate-200/90 bg-white px-3.5 sm:px-4.5 py-2 text-xs sm:text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="sortKey">
                                <option value="created_at">Sort: Date</option>
                                <option value="name">Sort: Name</option>
                                <option value="email">Sort: Email</option>
                                <option value="phone">Sort: Phone</option>
                            </select>
                            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200/90 bg-white px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                                x-on:click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8 9 4-4 4 4m0 6-4 4-4-4" />
                                </svg>
                                <span x-text="sortDir === 'asc' ? 'Ascending' : 'Descending'"></span>
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2 justify-between">
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-rose-100 bg-rose-50 px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-rose-600 hover:border-rose-200 hover:bg-rose-100"
                                x-on:click="clearFilters()">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m4 4 16 16" />
                                    <path d="M10 4h10v2a2 2 0 0 1-2 2h-2" />
                                    <path d="m8 8-4 4v2a2 2 0 0 0 2 2h6" />
                                </svg>
                                Reset
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white px-3 sm:px-3.5 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:border-indigo-200 hover:text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500"
                                @click="filtersOpen = false">
                                Done
                            </button>
                        </div>
                    </div>

                    <!-- Mobile search input -->
                    <div x-show="searchOpen" x-transition class="lg:hidden relative w-full">
                        <input type="text" placeholder="Search employee" class="w-full rounded-2xl border border-slate-200/90 bg-white py-2 sm:py-2.5 ps-9 sm:ps-10 pe-3 text-xs sm:text-sm font-medium text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            x-model.debounce.200ms="search" x-on:keydown.escape="search = ''" />
                        <span class="absolute left-3 top-2.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="flex-1 overflow-hidden">
                    <div class="overflow-x-auto lg:overflow-visible h-full">
                        <div class="max-h-full min-h-full overflow-y-auto rounded-b-2xl bg-white">
                            <table class="w-full divide-y divide-slate-100">
                                <thead class="sticky top-0 z-10 bg-slate-50 backdrop-blur text-left text-[11px] sm:text-xs font-semibold uppercase text-slate-500 shadow-[0_6px_12px_-12px_rgba(15,23,42,0.35)]">
                                    <tr>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Employee</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3 min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]">Division/Section/Unit/Office</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3 min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]">Email</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Phone</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3">Status</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3 min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]">Place Of Assignment</th>
                                        <th class="px-4 sm:px-6 py-2.5 sm:py-3 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white text-[11px] sm:text-xs text-slate-700">
                                    <template x-if="filteredSorted().length === 0">
                                        <tr>
                                            <td colspan="7" class="px-4 sm:px-6 py-8 text-center text-slate-400 text-sm">
                                                No employee registered yet.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="employee in filteredSorted()" :key="employee.id">
                                        <tr class="hover:bg-slate-50"
                                            x-data="{
                                                key: 'employee-details-' + employee.id,
                                                employee,
                                                statusClass() { return this.employee.status === 'Active' ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'; },
                                            }"
                                            x-init="window.addEventListener('employee-updated', e => { if (e.detail?.key === key) { employee = e.detail.employee; } })"
                                            x-cloak>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                                <div class="flex items-center gap-3">
                                                    <img :src="employee.avatar" :alt="employee.name + ' avatar'" class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover shadow-sm">
                                                    <div>
                                                        <p class="font-semibold text-slate-900 text-xs sm:text-sm whitespace-nowrap min-w-[9.5rem]" x-text="employee.name"></p>
                                                        <span class="text-slate-500 text-[11px] sm:text-xs" x-text="employee.type"></span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-normal break-words min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]" x-text="employee.unit"></td>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500 whitespace-normal break-words min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]" x-text="employee.email"></td>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4" x-text="employee.phone"></td>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] sm:text-xs font-semibold" :class="statusClass()" x-text="employee.status"></span>
                                            </td>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500 whitespace-normal break-words min-w-[14ch] sm:min-w-[18ch] md:min-w-[22ch] max-w-[32ch]" x-text="employee.location"></td>
                                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-center">
                                                <div class="inline-flex items-center gap-2 justify-center">
                                                    <button type="button" class="inline-flex items-center rounded-full border border-amber-200 px-3 py-1.5 text-[10px] sm:text-[11px] font-semibold text-amber-600 hover:bg-amber-50"
                                                        x-on:click.prevent="requestArchive(employee)" title="Archive">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M21 8v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8"/>
                                                            <rect x="1" y="3" width="22" height="5" rx="1" ry="1"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="inline-flex items-center rounded-full border border-indigo-200 px-3.5 sm:px-4 py-1.5 text-[10px] sm:text-[11px] font-semibold text-indigo-600 hover:bg-indigo-50"
                                                        x-on:click.prevent="window.dispatchEvent(new CustomEvent('open-modal', { detail: key }));">
                                                        View
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            @foreach ($employees as $employee)
                <x-view-user-modal :employee="$employee" :name="'employee-details-' . $employee['id']" :key="'employee-details-' . $employee['id']" width="2xl" :units="$units" />
            @endforeach

            <!-- Add Employee Modal -->
            <div x-show="addEmployeeOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4"
                x-transition.opacity @click.self="closeEmployee()">
                <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-slate-100"
                    x-transition.scale>
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <div>
                            <h2 class="text-lg uppercase text-slate-700 font-semibold">Add Employee</h2>
                            <p class="text-xs text-slate-500">Add a new employee.</p>
                        </div>
                        <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                            @click="closeEmployee()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-5 py-4 space-y-4">
                        <div class="space-y-2">
                            <label for="new-person-name" class="text-sm font-medium text-slate-700">Full name</label>
                            <input id="new-person-name" type="text" x-model="newEmployeeName" placeholder="e.g. Juan Dela Cruz"
                                class="uppercase w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                            <template x-if="employeeFieldErrors?.full_name">
                                <p class="text-sm text-rose-600" >The name has already been taken.</p>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                            @click="closeEmployee()">Cancel</button>
                        <button type="button"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="!newEmployeeName.trim() || savingEmployee"
                            @click="requestConfirm('employee')">
                            <span x-show="!savingEmployee">Save</span>
                            <span x-show="savingEmployee">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Confirm Modal -->
            <div x-show="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4"
                x-transition.opacity @click.self="closeConfirm()">
                <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-slate-100" x-transition.scale>
                    <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900" x-text="confirmTitle || 'Please confirm'"></h3>
                            <p class="text-sm text-slate-500" x-text="confirmBody || 'Are you sure?'"></p>
                        </div>
                        <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                            @click="closeConfirm()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-5 py-4 flex items-center justify-end gap-3">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                            @click="closeConfirm()">Not now</button>
                        <button type="button" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="confirmType === 'employee' && savingEmployee"
                            @click="confirmSubmit()">
                            <span x-text="(savingEmployee) ? 'Processing...' : 'Confirm'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Archive Confirm Modal -->
            <div x-show="archiveConfirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4"
                x-transition.opacity @click.self="closeArchiveConfirm()">
                <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-slate-100" x-transition.scale>
                    <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Archive Employee</h2>
                            <br>
                            <p class="text-sm text-slate-500">You are about to archive <span class="font-semibold text-slate-900" x-text="archiveTarget?.name"></span>. The employee will be moved to the archive page.</p>
                            <template x-if="archiveError">
                                <p class="mt-2 text-sm text-rose-600" x-text="archiveError"></p>
                            </template>
                        </div>
                        <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                            @click="closeArchiveConfirm()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                        
                    </div>
                    <div class="px-5 py-4 flex items-center justify-end gap-3">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                            @click="closeArchiveConfirm()">Cancel</button>
                        <button type="button" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="archiving" @click="performArchive()">
                            <span x-text="archiving ? 'Processing...' : 'Archive'"></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>