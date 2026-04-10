<x-app-layout>
    

    <div class="py-5 sm:py-10">
        <div class="mx-auto px-2 sm:px-6 lg:px-20 space-y-10">

            <section class="flex flex-col items-center gap-10 py-6">
                <div class="grid w-full max-w-9xl mx-auto grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
                    <a href="{{ route('manage-user') }}?status=permanent" class="rounded-2xl bg-gradient-to-r from-sky-500 to-blue-500 text-white shadow-md shadow-sky-200/40 border border-white/10 min-h-[8.5rem] w-full max-w-[320px] flex basis-1/3 sm:basis-1/3 md:basis-1/4 lg:basis-1/7 hover:shadow-lg hover:shadow-sky-200/60 transition-all duration-200 cursor-pointer">
                        <div class="p-4 sm:p-5 flex flex-col justify-between w-full">
                            <p class="text-base sm:text-lg font-semibold">Permanent Employees</p>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-round-icon lucide-users-round"><path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/></svg>
                                    </span>
                                    <p class="text-3xl sm:text-4xl font-semibold leading-none">{{ number_format($stats['totalEmployees'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('manage-user') }}?status=contract" class="rounded-2xl bg-gradient-to-r from-emerald-400 to-teal-500 text-white shadow-md shadow-emerald-200/40 border border-white/10 min-h-[8.5rem] w-full max-w-[320px] flex basis-1/3 sm:basis-1/3 md:basis-1/4 lg:basis-1/7 hover:shadow-lg hover:shadow-emerald-200/60 transition-all duration-200 cursor-pointer">
                        <div class="p-4 sm:p-5 flex flex-col justify-between w-full">
                            <p class="text-base sm:text-lg font-semibold">Contract of Service</p>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-search-icon lucide-user-round-search"><circle cx="10" cy="8" r="5"/><path d="M2 21a8 8 0 0 1 10.434-7.62"/><circle cx="18" cy="18" r="3"/><path d="m22 22-1.9-1.9"/></svg>
                                    </span>
                                    <p class="text-3xl sm:text-4xl font-semibold leading-none">{{ number_format($stats['verifiedEmployees'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('manage-user') }}?status=joborder" class="rounded-2xl bg-gradient-to-r from-purple-400 to-indigo-500 text-white shadow-md shadow-purple-200/40 border border-white/10 min-h-[8.5rem] w-full max-w-[320px] flex basis-1/3 sm:basis-1/3 md:basis-1/4 lg:basis-1/7 hover:shadow-lg hover:shadow-purple-200/60 transition-all duration-200 cursor-pointer">
                        <div class="p-4 sm:p-5 flex flex-col justify-between w-full">
                            <p class="text-base sm:text-lg font-semibold">Job Order</p>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-pen-icon lucide-user-round-pen"><path d="M2 21a8 8 0 0 1 10.821-7.487"/><path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><circle cx="10" cy="8" r="5"/></svg>
                                    </span>
                                    <p class="text-3xl sm:text-4xl font-semibold leading-none">{{ number_format($stats['jobOrderEmployees'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('pds.form', ['status' => 'pending']) }}" class="rounded-2xl bg-gradient-to-r from-amber-400 to-orange-400 text-white shadow-md shadow-amber-200/40 border border-white/10 min-h-[8.5rem] w-full max-w-[320px] flex basis-1/3 sm:basis-1/3 md:basis-1/4 lg:basis-1/7 hover:shadow-lg hover:shadow-amber-200/60 transition-all duration-200 cursor-pointer">
                        <div class="p-4 sm:p-5 flex flex-col justify-between w-full">
                            <p class="text-base sm:text-lg font-semibold">Pending PDS</p>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock4-icon lucide-clock-4"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>
                                    </span>
                                    <p class="text-3xl sm:text-4xl font-semibold leading-none">{{ number_format($stats['pendingPds'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('pds.form', ['status' => 'approved']) }}" class="rounded-2xl bg-gradient-to-r from-sky-400 to-cyan-400 text-white shadow-md shadow-sky-200/40 border border-white/10 min-h-[8.5rem] w-full max-w-[320px] flex basis-1/3 sm:basis-1/3 md:basis-1/4 lg:basis-1/7 hover:shadow-lg hover:shadow-sky-200/60 transition-all duration-200 cursor-pointer">
                        <div class="p-4 sm:p-5 flex flex-col justify-between w-full">
                            <p class="text-base sm:text-lg font-semibold">Approved PDS</p>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-todo-icon lucide-list-todo"><path d="M13 5h8"/><path d="M13 12h8"/><path d="M13 19h8"/><path d="m3 17 2 2 4-4"/><rect x="3" y="4" width="6" height="6" rx="1"/></svg>
                                    </span>
                                    <p class="text-3xl sm:text-4xl font-semibold leading-none">{{ number_format($stats['approvedPds'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('pds.form', ['status' => 'rejected']) }}" class="rounded-2xl bg-gradient-to-r from-rose-400 to-pink-500 text-white shadow-md shadow-rose-200/40 border border-white/10 min-h-[8.5rem] w-full max-w-[320px] flex basis-1/3 sm:basis-1/3 md:basis-1/4 lg:basis-1/7 hover:shadow-lg hover:shadow-rose-200/60 transition-all duration-200 cursor-pointer">
                        <div class="p-4 sm:p-5 flex flex-col justify-between w-full">
                            <p class="text-base sm:text-lg font-semibold">Rejected PDS</p>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl bg-white/15">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x-icon lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                    </span>
                                    <p class="text-3xl sm:text-4xl font-semibold leading-none">{{ number_format($stats['rejectedPds'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                
            </section>

            <div x-data="dashboardPreview(@js($stats['recentSubmissions']))" x-init="init()" class="space-y-12">

            <!-- Recent submissions table helps admins monitor latest activity -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="px-4 sm:px-8 py-3 sm:py-4 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <p class="text-base sm:text-lg font-semibold text-slate-900">Latest PDS submissions</p>
                        <p class="text-xs sm:text-sm text-slate-500">Track recent submissions</p>
                    </div>
                    <span class="text-xs sm:text-sm text-slate-500 font-semibold">Updated {{ now()->format('M d, Y') }}</span>
                </div>


                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-left text-xs sm:text-sm font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Employee</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Division/Section/Unit/Office</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Email</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Phone</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Place of Assignment</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Date Submitted</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-xs sm:text-sm text-slate-700">
                            @forelse ($stats['recentSubmissions'] as $submission)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $submission['avatar'] }}" alt="{{ $submission['name'] }} avatar" class="h-9 w-9 sm:h-10 sm:w-10 rounded-full object-cover shadow-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900 text-sm sm:text-base">{{ $submission['name'] }}</p>
                                                <span class="text-slate-500 text-xs sm:text-sm">{{ $submission['type'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4">{{ $submission['unit'] }}</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500">{{ $submission['email'] }}</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4">{{ $submission['phone'] }}</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500">{{ $submission['location'] }}</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500">{{ $submission['submitted_at'] }}</td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-center">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-indigo-200 px-3 sm:px-4 py-1.5 text-[11px] sm:text-xs font-semibold text-indigo-600 hover:bg-indigo-50"
                                            @click.prevent="openById({{ $submission['id'] }}, @js($submission))"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 sm:px-6 py-8 text-center text-slate-400 text-sm">
                                        No submissions yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- History of admin activity -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="px-4 sm:px-8 py-3 sm:py-4 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <p class="text-base sm:text-lg font-semibold text-slate-900">Admin History Activity</p>
                        <p class="text-xs sm:text-sm text-slate-500">Track recent activity</p>
                    </div>
                    <span class="text-xs sm:text-sm text-slate-500 font-semibold">Updated {{ now()->format('M d, Y') }}</span>
                </div>

                <div class="overflow-hidden">
                    <div class="max-h-[580px] overflow-y-auto">
                        <div class="min-w-full">
                            <div class="overflow-x-auto">
                                <table class="w-full divide-y divide-slate-100">
                                    <thead class="bg-slate-50 text-left text-xs sm:text-sm font-semibold uppercase text-slate-500">
                                        <tr>
                                            <th class="px-4 sm:px-6 py-2.5 sm:py-3">Admin</th>
                                            <th class="px-4 sm:px-6 py-2.5 sm:py-3">Role</th>
                                            <th class="px-4 sm:px-6 py-2.5 sm:py-3">Activity</th>
                                            <th class="px-4 sm:px-6 py-2.5 sm:py-3">Employee</th>
                                            <th class="px-4 sm:px-6 py-2.5 sm:py-3">Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white text-xs sm:text-sm text-slate-700">
                                        @forelse ($stats['recentActivityLogs'] as $log)
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                                    <p class="font-semibold text-slate-900">{{ $log['admin_name'] }}</p>
                                                </td>
                                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                                    @php
                                                        $roleKey = strtolower($log['admin_role']);
                                                        $roleBadge = match(true) {
                                                            str_contains($roleKey, 'main') => 'bg-indigo-100 text-indigo-700',
                                                            default => 'bg-slate-100 text-slate-600',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $roleBadge }}">
                                                        {{ $log['admin_role'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 sm:px-6 py-3 sm:py-4 max-w-xs">
                                                    @php
                                                        $actionType = $log['action_type'];
                                                        $actionBadge = match($actionType) {
                                                            'archive'              => 'bg-amber-100 text-amber-700',
                                                            'unarchive'            => 'bg-teal-100 text-teal-700',
                                                            'delete', 'delete_admin' => 'bg-rose-100 text-rose-700',
                                                            'update', 'update_admin' => 'bg-sky-100 text-sky-700',
                                                            'create_admin'         => 'bg-emerald-100 text-emerald-700',
                                                            'pds_status'           => 'bg-purple-100 text-purple-700',
                                                            'profile_edit_approved'=> 'bg-green-100 text-green-700',
                                                            'profile_edit_rejected'=> 'bg-red-100 text-red-700',
                                                            default                => 'bg-slate-100 text-slate-600',
                                                        };
                                                        $actionLabel = match($actionType) {
                                                            'archive'              => 'Archive',
                                                            'unarchive'            => 'Unarchive',
                                                            'delete'               => 'Delete',
                                                            'delete_admin'         => 'Delete Admin',
                                                            'update'               => 'Update',
                                                            'update_admin'         => 'Update Admin',
                                                            'create_admin'         => 'Create Admin',
                                                            'pds_status'           => 'PDS Status',
                                                            'profile_edit_approved'=> 'Edit Approved',
                                                            'profile_edit_rejected'=> 'Edit Rejected',
                                                            default                => ucfirst($actionType),
                                                        };
                                                    @endphp
                                                    <div class="flex flex-col gap-1">
                                                        <span class="inline-flex items-center self-start rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $actionBadge }}">
                                                            {{ $actionLabel }}
                                                        </span>
                                                        <span class="text-slate-600 leading-snug">{{ $log['activity'] }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                                    @if($log['target_user_name'] !== '—')
                                                        <p class="font-semibold text-slate-900">{{ $log['target_user_name'] }}</p>
                                                        <span class="text-slate-400 text-xs">{{ $log['target_user_type'] }}</span>
                                                    @else
                                                        <span class="text-slate-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500 whitespace-nowrap">{{ $log['date_time'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 sm:px-6 py-8 text-center text-slate-400 text-sm">
                                                    No activity recorded yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-pds-preview x-show="modalOpen" @close="close()" class="!mt-0" />
            </div>

        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    function dashboardPreview(submissions = []) {
        return {
            submissions,
            modalOpen: false,
            selected: null,
            open(submission) {
                this.selected = submission;
                this.modalOpen = true;
            },
            close() {
                this.modalOpen = false;
                this.selected = null;
            },
            requestConfirm() {
                window.location.href = '/pds-form';
            },
            downloadPds() {
                if (this.selected?.user_id) {
                    window.open(`/pds-preview/${this.selected.user_id}?ts=${Date.now()}`, '_blank');
                }
            },
        };
    }
</script>
@endpush
