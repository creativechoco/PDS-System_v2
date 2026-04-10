<x-app-layout>
    <div class="py-10"
        x-data="{
            search: '',
            filterStatus: '',
            sortKey: 'created_at',
            sortDir: 'desc',
            admins: @js($admins),
            loggedInRole: @js($loggedInRole),
            deleteConfirmOpen: false,
            deleteTarget: null,
            deleteError: '',
            deleting: false,

            addAdminOpen: false,
            adminName: '',
            adminEmail: '',
            adminPassword: '',
            adminPasswordConfirm: '',
            savingAdmin: false,
            adminError: '',
            adminFieldErrors: {},
            norm(v) { return (v ?? '').toString().trim().toLowerCase(); },
            isMainAdmin() { return this.norm(this.loggedInRole) === 'main admin'; },
            init() {
                window.addEventListener('admin-updated', (e) => {
                    const updated = e.detail?.admin;
                    if (!updated?.email) return;
                    const target = this.norm(updated.email);
                    this.admins = this.admins.map(a => this.norm(a.email) === target ? updated : a);
                });

                window.addEventListener('admin-deleted', (e) => {
                    const id = e.detail?.id;
                    if (id !== undefined && id !== null) {
                        this.removeAdminById(id);
                    }
                });
            },
            openAdmin() {
                this.adminError = '';
                this.adminFieldErrors = {};
                this.savingAdmin = false;
                this.adminName = '';
                this.adminEmail = '';
                this.adminPassword = '';
                this.adminPasswordConfirm = '';
                this.addAdminOpen = true;
            },
            closeAdmin() {
                this.addAdminOpen = false;
            },
            filteredSorted() {
                return this.admins
                    .filter(a => {
                        const q = this.search.toLowerCase();
                        const matchesSearch = !q || this.norm(a.name).includes(q) || this.norm(a.email).includes(q) || this.norm(a.role).includes(q);

                        const statusFilter = this.norm(this.filterStatus);
                        const matchesStatus = !statusFilter || this.norm(a.status) === statusFilter;
                        return matchesSearch && matchesStatus;
                    })
                    .sort((a, b) => {
                        const dir = this.sortDir === 'asc' ? 1 : -1;
                        const key = this.sortKey;

                        const avRaw = a[key];
                        const bvRaw = b[key];

                        // Special handling for created_at date string
                        if (key === 'created_at') {
                            const avDate = new Date(avRaw || '').getTime() || 0;
                            const bvDate = new Date(bvRaw || '').getTime() || 0;
                            const diff = avDate - bvDate;
                            if (diff !== 0) return diff * dir;
                        }

                        const av = this.norm(avRaw);
                        const bv = this.norm(bvRaw);
                        const primary = av.localeCompare(bv);
                        if (primary !== 0) return primary * dir;

                        return this.norm(a.name).localeCompare(this.norm(b.name)) * dir;
                    });
            },
            clearFilters() {
                this.search = '';
                this.filterStatus = '';
                this.sortKey = 'name';
                this.sortDir = 'asc';
            },
            requestDelete(admin) {
                this.deleteTarget = admin;
                this.deleteError = '';
                this.deleting = false;
                this.deleteConfirmOpen = true;
            },
            closeDeleteConfirm() {
                this.deleteConfirmOpen = false;
                this.deleteTarget = null;
                this.deleteError = '';
                this.deleting = false;
            },
            removeAdminById(id) {
                const targetId = Number(id);
                this.admins = this.admins.filter(a => Number(a.id) !== targetId);
            },
            addAdminToList(admin) {
                if (!admin) return;
                const norm = (v) => (v ?? '').toString().trim();
                const fallbackAvatar = '{{ asset('images/avatar.jpg') }}';
                this.admins = [
                    ...this.admins,
                    {
                        id: admin.id,
                        name: norm(admin.name),
                        email: norm(admin.email),
                        role: admin.role || 'Admin User',
                        status: admin.status || 'Active',
                        created_at: admin.created_at || new Date().toISOString().slice(0, 10),
                        avatar: admin.avatar || fallbackAvatar,
                    }
                ];
            },
            async submitAdmin() {
                if (this.savingAdmin) return;
                this.adminError = '';
                this.adminFieldErrors = {};
                this.savingAdmin = true;

                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(this.adminEmail.trim())) {
                    this.savingAdmin = false;
                    this.adminFieldErrors = { email: ['Please enter a valid email address.'] };
                    this.adminError = 'Please fix the errors and try again.';
                    return;
                }

                if (this.adminPassword.length < 8) {
                    this.savingAdmin = false;
                    this.adminFieldErrors = { password: ['Password must be at least 8 characters.'] };
                    this.adminError = 'Please fix the errors and try again.';
                    return;
                }

                if (this.adminPassword !== this.adminPasswordConfirm) {
                    this.savingAdmin = false;
                    this.adminFieldErrors = { password: ['Passwords do not match.'] };
                    this.adminError = 'Please fix the errors and try again.';
                    return;
                }

                try {
                    const res = await fetch('{{ route('admin-users.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            name: this.adminName,
                            email: this.adminEmail,
                            password: this.adminPassword,
                            password_confirmation: this.adminPasswordConfirm,
                        })
                    });

                    const data = await res.json().catch(() => null);
                    const errors = data?.errors || {};

                    if (!res.ok) {
                        this.adminFieldErrors = errors;
                        let msg = data?.message;
                        if (!msg && res.status === 422) {
                            const combined = Object.values(errors).flat().join(' ') || '';
                            if (combined.toLowerCase().includes('already been taken')) {
                                msg = 'Name or email is already in use.';
                            } else {
                                msg = 'Please fix the highlighted errors.';
                            }
                        }
                        if (!msg && (res.status === 401 || res.status === 403)) {
                            msg = 'You are not authorized to create admins.';
                        }
                        if (!msg) {
                            msg = res.status >= 500 ? 'Server error. Please try again.' : 'Unable to create admin. Please try again.';
                        }
                        throw new Error(msg);
                    }

                    this.addAdminToList(data?.admin);
                    this.addAdminOpen = false;
                    this.adminName = '';
                    this.adminEmail = '';
                    this.adminPassword = '';
                    this.adminPasswordConfirm = '';
                    this.adminFieldErrors = {};
                } catch (err) {
                    this.adminError = err.message || 'Unable to create admin.';
                } finally {
                    this.savingAdmin = false;
                }
            },
            async performDelete() {
                if (!this.deleteTarget || this.deleting) return;
                this.deleting = true;
                this.deleteError = '';

                try {
                    const res = await fetch('/admin-users/' + this.deleteTarget.id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                        }
                    });
                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        throw new Error(data?.message || 'Failed to delete admin.');
                    }
                    this.removeAdminById(this.deleteTarget.id);
                    window.dispatchEvent(new CustomEvent('admin-deleted', { detail: { id: this.deleteTarget.id } }));
                    this.closeDeleteConfirm();
                } catch (err) {
                    this.deleteError = err.message || 'Failed to delete admin.';
                } finally {
                    this.deleting = false;
                }
            }
        }"
        x-init="init()"
        x-on:open-delete.window="requestDelete($event.detail)">

        <div class="mx-auto sm:px-6 lg:px-20 space-y-8 flex flex-col h-[calc(100vh-180px)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide text-indigo-500 font-semibold">Admin Users</p>
                    <h1 class="text-2xl font-bold text-slate-900">Manage Admin Accounts</h1>
                    <p class="text-slate-500 text-sm">Review admin roles, status, and contact details.</p>
                </div>
                <template x-if="isMainAdmin()">
                    <div class="flex gap-3">
                        <button type="button"
                            @click="openAdmin()"
                            class="inline-flex items-center rounded-xl border border-indigo-200 px-4 py-2 text-sm font-medium text-indigo-600 bg-white hover:bg-indigo-50 shadow-sm">
                            Add Admin
                        </button>
                    </div>
                </template>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100 flex flex-col flex-1 min-h-0">

                <div class="px-8 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between border-b border-slate-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <select class="rounded-full border border-slate-200/90 w-28 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="filterStatus">
                            <option value="">Status: All</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>

                        <select class="rounded-full border border-slate-200/90 w-32 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="sortKey">
                            <option value="created_at">Sort: Created</option>
                            <option value="name">Name</option>
                            <option value="email">Email</option>
                            <option value="role">Role</option>
                            <option value="status">Status</option>
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

                    <div class="relative w-full lg:w-72">
                        <input type="text" placeholder="Search admins" class="w-full rounded-2xl border border-slate-200/90 bg-white py-2.5 ps-10 pe-3 text-sm font-medium text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            x-model.debounce.200ms="search" x-on:keydown.escape="search = ''" />
                        <span class="absolute left-3 top-2.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0-6.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="overflow-x-auto h-full">
                        <div class="max-h-full min-h-full overflow-y-auto rounded-b-2xl bg-white">
                            <table class="w-full divide-y divide-slate-100">
                                <thead class="sticky top-0 z-10 bg-slate-50 backdrop-blur text-left text-xs font-semibold uppercase text-slate-500 shadow-[0_6px_12px_-12px_rgba(15,23,42,0.35)]">
                                <tr>
                                    <th class="px-6 py-3">Admin</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Role</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Created</th>
                                    <th class="px-6 py-3 text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                                <template x-for="admin in filteredSorted()" :key="admin.id">
                                    <tr class="hover:bg-slate-50"
                                        x-data="{
                                            key: 'admin-details-' + admin.id,
                                            admin,
                                            statusClass() { return this.norm(admin.status) === 'active' ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'; },
                                            norm(v) { return (v ?? '').toString().trim().toLowerCase(); }
                                        }"
                                        x-init="window.addEventListener('admin-updated', e => { if (e.detail?.key === key) { admin = e.detail.admin; } })"
                                        x-cloak>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <img :src="admin.avatar" :alt="admin.name + ' avatar'" class="h-10 w-10 rounded-full object-cover shadow-sm">
                                                <div>
                                                    <p class="font-semibold text-slate-900" x-text="admin.name"></p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-slate-500" x-text="admin.email"></td>
                                        <td class="px-6 py-4" x-text="admin.role"></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                                :class="statusClass()"
                                                x-text="admin.status"></span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500" x-text="admin.created_at"></td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="inline-flex items-center gap-2 justify-center">
                                                <template x-if="isMainAdmin()">
                                                    <button type="button" class="inline-flex items-center rounded-full border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                                                        x-on:click.prevent="$dispatch('open-delete', admin)" title="Delete">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M3 6h18" />
                                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                            <path d="M10 11v6" />
                                                            <path d="M14 11v6" />
                                                            <path d="M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14" />
                                                        </svg>
                                                    </button>
                                                </template>
                                                <button type="button" class="inline-flex items-center rounded-full border border-indigo-200 px-4 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50"
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
            </div>
        </div>

        <!-- Add Admin Modal -->
        <div x-show="addAdminOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4"
            x-transition.opacity @click.self="closeAdmin()" x-cloak>
            <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-slate-100"
                x-transition.scale>
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <div>
                        <h2 class="text-lg uppercase text-slate-700 font-semibold">Add Admin</h2>
                        <p class="text-xs text-slate-500">Create an admin account with credentials.</p>
                    </div>
                    <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                        @click="closeAdmin()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-5 py-4 space-y-4">
                    <div class="space-y-2">
                        <label for="admin-name" class="text-sm font-medium text-slate-700">Full name</label>
                        <input id="admin-name" type="text" x-model="adminName" placeholder="e.g. Admin User"
                            class="uppercase w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        <template x-if="adminFieldErrors?.name">
                            <p class="text-sm text-rose-600" x-text="adminFieldErrors.name[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label for="admin-email" class="text-sm font-medium text-slate-700">Email</label>
                        <input id="admin-email" type="email" x-model="adminEmail" placeholder="admin@example.com"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        <template x-if="adminFieldErrors?.email">
                            <p class="text-sm text-rose-600" x-text="adminFieldErrors.email[0]"></p>
                        </template>
                        <template x-if="adminFieldErrors?.email === undefined && adminEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(adminEmail)">
                            <p class="text-sm text-rose-600">Please enter a valid email address.</p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label for="admin-password" class="text-sm font-medium text-slate-700">Password</label>
                        <input id="admin-password" type="password" x-model="adminPassword" placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        <template x-if="adminFieldErrors?.password">
                            <p class="text-sm text-rose-600" x-text="adminFieldErrors.password[0]"></p>
                        </template>
                        <template x-if="adminFieldErrors?.password === undefined && adminPassword && adminPassword.length < 8">
                            <p class="text-sm text-rose-600">Password must be at least 8 characters.</p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label for="admin-password-confirm" class="text-sm font-medium text-slate-700">Confirm Password</label>
                        <input id="admin-password-confirm" type="password" x-model="adminPasswordConfirm" placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" />
                        <template x-if="adminPassword && adminPasswordConfirm && adminPassword !== adminPasswordConfirm">
                            <p class="text-sm text-rose-600">Passwords do not match.</p>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                        @click="closeAdmin()">Cancel</button>
                    <button type="button"
                        class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="savingAdmin || !(adminName.trim() && adminEmail.trim() && adminPassword && adminPasswordConfirm)"
                        @click="submitAdmin()">
                        <span x-show="!savingAdmin">Create</span>
                        <span x-show="savingAdmin">Creating...</span>
                    </button>
                </div>
            </div>
        </div>
        @foreach ($admins as $admin)
            <x-view-admin-modal :admin="$admin" :name="'admin-details-' . $admin['id']" :key="'admin-details-' . $admin['id']" :loggedInRole="$loggedInRole" width="2xl" />
        @endforeach

        <!-- Delete Confirm Modal -->
        <div x-show="deleteConfirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4"
            x-transition.opacity @click.self="closeDeleteConfirm()" x-cloak>
            <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl border border-slate-100" x-transition.scale>
                <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Delete Admin</h2>
                        <br>
                        <p class="text-sm text-slate-500">You are about to permanently remove <span class="font-semibold text-slate-900" x-text="deleteTarget?.name"></span>. This action cannot be undone.</p>
                        <template x-if="deleteError">
                            <p class="mt-2 text-sm text-rose-600" x-text="deleteError"></p>
                        </template>
                    </div>
                    <button type="button" class="rounded-full p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100"
                        @click="closeDeleteConfirm()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-5 py-4 flex items-center justify-end gap-3">
                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-white"
                        @click="closeDeleteConfirm()">Cancel</button>
                    <button type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="deleting" @click="performDelete()">
                        <span x-text="deleting ? 'Processing...' : 'Delete'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
