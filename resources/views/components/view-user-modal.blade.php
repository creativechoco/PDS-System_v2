@props([
    'employee',
    'name',
    'key' => null,
    'width' => '2xl',
    'units' => [],
])

<x-modal :name="$name" :max-width="$width">
    <div class="p-5 sm:p-8 lg:p-10 space-y-6 w-full max-w-full"
        x-data="{
            key: @js($key ?? $name),
            employee: @js($employee),
            units: @js($units),
            working: {},
            init() { this.reset(); },
            reset() { this.working = JSON.parse(JSON.stringify(this.employee)); },
            saving: false,
            saveError: '',
            requestWorking: false,
            requestError: '',
            requestSuccess: '',
            requestStatusLabel() {
                const s = this.working.edit_request?.status;
                if (s === 'approved') return 'Approved';
                if (s === 'rejected') return 'Rejected';
                if (s === 'pending') return 'Pending';
                return 'No request';
            },
            requestStatusClass() {
                const s = this.working.edit_request?.status;
                if (s === 'approved') return 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                if (s === 'rejected') return 'bg-rose-50 text-rose-700 border border-rose-100';
                if (s === 'pending') return 'bg-amber-50 text-amber-700 border border-amber-100';
                return 'bg-slate-50 text-slate-600 border border-slate-100';
            },
            async updateRequest(action) {
                if (!this.working.edit_request?.id || this.requestWorking) return;
                this.requestWorking = true;
                this.requestError = '';
                this.requestSuccess = '';
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                try {
                    const url = action === 'approve'
                        ? `/profile-edit-requests/${this.working.edit_request.id}/approve`
                        : `/profile-edit-requests/${this.working.edit_request.id}/reject`;
                    const payload = action === 'reject' ? { remarks: this.working.edit_request?.remarks || '' } : {};
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        throw new Error(data?.message || 'Failed to update request.');
                    }
                    this.working.edit_request.status = action === 'approve' ? 'approved' : 'rejected';
                    this.requestSuccess = data?.message || 'Request updated.';
                    window.dispatchEvent(new CustomEvent('employee-request-updated', { detail: { id: this.working.id, status: this.working.edit_request.status } }));
                } catch (e) {
                    this.requestError = e.message || 'Failed to update request.';
                } finally {
                    this.requestWorking = false;
                }
            },
            save() {
                if (this.saving) return;
                this.saving = true;
                this.saveError = '';

                const payload = {
                    name: this.working.name,
                    unit: this.working.unit,
                    email: this.working.email,
                    phone: this.working.phone,
                    type: this.working.type,
                    status: this.working.status,
                    location_assigned: this.working.location,
                };

                fetch('/manage-user/' + this.working.id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        const errors = data?.errors || {};
                        const msg = Object.values(errors).flat().join(' ') || data?.message || 'Failed to update user.';
                        throw new Error(msg);
                    }
                    this.employee = JSON.parse(JSON.stringify(this.working));
                    window.dispatchEvent(new CustomEvent('employee-updated', { detail: { key: this.key, employee: this.employee } }));
                    this.$dispatch('close');
                })
                .catch(err => {
                    this.saveError = err.message || 'Failed to update user.';
                })
                .finally(() => {
                    this.saving = false;
                });
            },
            statusClass() { return this.working.status === 'Active' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; },
            typeClass() { 
                if (this.working.type === 'Permanent Employee') return 'bg-emerald-50 text-emerald-600';
                if (this.working.type === 'Contract of Service') return 'bg-indigo-50 text-indigo-600';
                if (this.working.type === 'Job Order') return 'bg-purple-50 text-purple-600';
                return 'bg-slate-50 text-slate-600';
            },
        }">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 w-full max-w-full">
                <div class="flex items-center gap-4 sm:gap-5 min-w-0">
                    <div class="relative h-24 w-24 sm:h-28 sm:w-28 lg:h-30 lg:w-30 rounded-full overflow-hidden border border-slate-200 shadow flex-shrink-0">
                        <img :src="working.avatar"
                            :alt="working.name + ' avatar'" 
                            class="object-cover h-full w-full">
                        
                    </div>
                    <div class="space-y-1.5 sm:space-y-2">
                        <div class="flex-col gap-1">
                            <input type="text" class="px-2 py-1 text-xl sm:text-2xl lg:text-3xl font-semibold text-slate-900 border-slate-200 rounded-xl p-0 focus:ring-0 focus:outline-none uppercase truncate w-full max-w-full"
                                x-model="working.name"
                                x-on:input="working.name = (working.name || '').toUpperCase()" />
                        </div>
                        <div class="flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                            <span class="inline-flex items-center rounded-full px-2.5 sm:px-3 py-1 text-[11px] sm:text-xs font-semibold" :class="typeClass()" x-text="working.type"></span>
                        </div>
                        <div class="flex items-center gap-2 text-xs sm:text-sm text-slate-500">
                            <span class="inline-flex items-center rounded-full px-2.5 sm:px-3 py-1 text-[11px] sm:text-xs font-semibold" x-text="working.unit"></span>
                        </div>
                    </div>
                </div>
            </div>

        <div class="grid gap-3 sm:gap-4 text-xs sm:text-sm text-slate-700 min-w-0">
            

            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 min-w-0">
                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500 text-[11px] sm:text-xs">Email</span>
                    <input type="email" class="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:text-base focus:border-indigo-500 focus:ring-indigo-500 w-full max-w-full truncate" x-model="working.email" />
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500 text-[11px] sm:text-xs">Phone</span>
                    <input type="tel"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:text-base focus:border-indigo-500 focus:ring-indigo-500 w-full max-w-full truncate"
                        x-model="working.phone"
                        maxlength="11"
                        pattern="\d{11}"
                        inputmode="numeric"
                        x-on:input="working.phone = (working.phone || '').replace(/[^0-9]/g, '').slice(0, 11)"
                    />
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4 min-w-0">
                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500 text-[11px] sm:text-xs">Employment Status</span>
                    <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:text-base focus:border-indigo-500 focus:ring-indigo-500 w-full max-w-full" x-model="working.type">
                        <option value="Permanent Employee">Permanent Employee</option>
                        <option value="Contract of Service">Contract of Service</option>
                        <option value="Job Order">Job Order</option>
                    </select>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="font-semibold text-slate-500 text-[11px] sm:text-xs">Status</span>
                    <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:text-base focus:border-indigo-500 focus:ring-indigo-500 w-full max-w-full" x-model="working.status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </label>
            </div>

            <label class="flex flex-col gap-1">
                <span class="font-semibold text-slate-500 text-[11px] sm:text-xs">Place of Assignment</span>
                <input type="text" class="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:text-base focus:border-indigo-500 focus:ring-indigo-500 uppercase w-full max-w-full truncate" x-model="working.location"
                x-on:input="working.location = (working.location || '').toUpperCase()" />
            </label>

            <label class="flex flex-col gap-1">
                <span class="font-semibold text-slate-500 text-[11px] sm:text-xs">Division/Section/Unit/Office</span>
                <select class="rounded-xl border border-slate-200 px-3 py-2 text-sm sm:text-base focus:border-indigo-500 focus:ring-indigo-500 w-full max-w-full truncate" x-model="working.unit">
                    <option value="" disabled>Select Division/Section/Unit/Office</option>
                    <template x-for="option in units" :key="option">
                        <option :value="option" x-text="option"></option>
                    </template>
                </select>
            </label>

            <template x-if="working.edit_request && working.edit_request.status === 'pending'">
                <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 via-white to-amber-50 px-3.5 py-4 shadow-sm">
                    <div class="flex items-start gap-3 flex-1 min-w-[220px]">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm sm:text-base uppercase tracking-wide text-amber-700 font-semibold">Profile Edit Request</span>
                            </div>
                            <p class="text-sm sm:text-base font-medium text-slate-700">Employee wants to edit its profile.</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-end min-w-[180px] ml-auto">
                        <div class="flex items-center gap-2" x-show="working.edit_request?.status === 'pending'" x-cloak>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-sm sm:text-base font-semibold text-slate-700 hover:bg-white disabled:opacity-60"
                                :disabled="requestWorking"
                                x-on:click="updateRequest('reject')">
                                Reject
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-3 py-1.5 text-sm sm:text-base font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
                                :disabled="requestWorking"
                                x-on:click="updateRequest('approve')">
                                Accept
                            </button>
                        </div>
                        <div class="flex flex-col text-right text-[11px] min-w-[120px]">
                            <template x-if="requestSuccess">
                                <span class="text-emerald-600" x-text="requestSuccess"></span>
                            </template>
                            <template x-if="requestError">
                                <span class="text-rose-600" x-text="requestError"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

        </div>

        <template x-if="saveError">
            <p class="text-sm text-rose-600" x-text="saveError"></p>
        </template>

        <div class="flex items-center justify-between gap-3 pt-2 text-sm sm:text-base">
            <div class="flex items-center gap-4 sm:gap-6">
                <button type="button" class="text-xs sm:text-sm font-medium text-slate-500 hover:text-slate-700" x-on:click="reset()">Undo</button>
            </div>
            <div class="flex gap-2 sm:gap-3">
                <x-secondary-button class="px-4 py-2 text-xs sm:text-sm" x-on:click="reset(); $dispatch('close')">Cancel</x-secondary-button>
                <x-primary-button class="px-4 py-2 text-xs sm:text-sm" x-on:click="save()" x-bind:disabled="saving">
                    <span x-show="!saving">Save changes</span>
                    <span x-show="saving">Saving...</span>
                </x-primary-button>
            </div>
        </div>
    </div>
</x-modal>
