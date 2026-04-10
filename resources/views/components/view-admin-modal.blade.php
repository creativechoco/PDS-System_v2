@props([
    'admin',
    'name',
    'key' => null,
    'loggedInRole' => null,
    'width' => '2xl',
])

<x-modal :name="$name" :max-width="$width">
    <div class="p-10 space-y-6"
        x-data="{
            key: @js($key ?? $name),
            admin: @js($admin),
            working: {},
            loading: false,
            loadError: '',
            saving: false,
            saveError: '',
            loggedInRole: @js($loggedInRole),
            init() { this.reset(); },
            reset() { this.working = JSON.parse(JSON.stringify(this.admin)); },
            isMainAdmin() { 
                return (this.loggedInRole || '').toString().trim().toLowerCase() === 'main admin'; 
            },
            async loadFresh() {
                if (!this.admin?.id) return;
                this.loading = true;
                this.loadError = '';
                try {
                    const res = await fetch('/admin-users/' + this.admin.id, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data?.message || 'Failed to load admin.');
                    this.admin = data;
                    this.reset();
                } catch (err) {
                    this.loadError = err.message || 'Failed to load admin.';
                } finally {
                    this.loading = false;
                }
            },
            async save() {
                if (!this.admin?.id || this.saving) return;
                this.saving = true;
                this.saveError = '';

                const payload = {
                    name: this.working.name,
                    email: this.working.email,
                    role: (this.working.role || '').toLowerCase(),
                    status: (this.working.status || '').toLowerCase(),
                };

                try {
                    const res = await fetch('/admin-users/' + this.admin.id, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        const errors = data?.errors || {};
                        const msg = Object.values(errors).flat().join(' ') || data?.message || 'Failed to update admin.';
                        throw new Error(msg);
                    }

                    this.admin = data.admin;
                    this.reset();
                    window.dispatchEvent(new CustomEvent('admin-updated', { detail: { key: this.key, admin: this.admin } }));
                    this.$dispatch('close');
                } catch (err) {
                    this.saveError = err.message || 'Failed to update admin.';
                } finally {
                    this.saving = false;
                }
            },
            statusClass() { return this.working.status === 'Active' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; },
            roleClass() { return this.working.role === 'Main Admin' ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-700'; },
        }"
        x-init="window.addEventListener('open-modal', (e) => { if (e.detail === key) { loadFresh(); } })">
            <div class="flex items-center gap-4">
                <div class="relative h-20 w-20">
                    <img :src="working.avatar" :alt="working.name + ' avatar'"
                        class="h-20 w-20 rounded-full object-cover shadow">
                </div>
                <div class="space-y-2 flex-1">
                    <div class="flex-col gap-1">
                        <input type="text" class="px-2 py-1 text-2xl font-semibold text-slate-900 border-slate-200 rounded-xl p-0 focus:ring-0 focus:outline-none"
                            x-model="working.name" :readonly="!isMainAdmin()" />
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" :class="roleClass()" x-text="working.role"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <span x-text="working.status"></span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 text-sm text-slate-700">
                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Email</span>
                    <input type="email" class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.email" :disabled="!isMainAdmin()" />
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Role</span>
                    <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-600" x-model="working.role" readonly />
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Status</span>
                    <select class="rounded-xl border border-slate-200 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500" x-model="working.status" :disabled="!isMainAdmin()">
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500">Created</span>
                    <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 text-slate-500" x-model="working.created_at" readonly />
                </label>
            </div>

            <template x-if="saveError">
                <p class="text-sm text-rose-600" x-text="saveError"></p>
            </template>

            <template x-if="isMainAdmin()">
                <div class="flex items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-6">
                        <button type="button" class="text-sm font-medium text-slate-500 hover:text-slate-700" x-on:click="reset()">Reset</button>
                    </div>
                    <div class="flex gap-3">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                        <x-primary-button x-on:click="save()" x-bind:disabled="saving">
                            <span x-show="!saving">Save changes</span>
                            <span x-show="saving">Saving...</span>
                        </x-primary-button>
                    </div>
                </div>
            </template>

            <template x-if="!isMainAdmin()">
                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-secondary-button x-on:click="$dispatch('close')">Close</x-secondary-button>
                </div>
            </template>
    </div>
</x-modal>
