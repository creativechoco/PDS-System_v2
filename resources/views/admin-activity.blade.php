<x-app-layout>

    <div class="py-5 sm:py-10">
        <div class="mx-auto px-2 sm:px-6 lg:px-20 space-y-6">

            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Admin History Activity</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Full log of all admin actions performed in the system.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    Back to Dashboard
                </a>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.activity.index') }}" class="bg-white shadow-sm sm:rounded-2xl border border-slate-100 px-4 sm:px-6 py-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Search</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Admin name, employee, activity..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        />
                    </div>
                    <div class="min-w-[160px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Action Type</label>
                        <select name="action_type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">All Actions</option>
                            <option value="update"               {{ request('action_type') === 'update'                ? 'selected' : '' }}>Update Employee</option>
                            <option value="archive"              {{ request('action_type') === 'archive'               ? 'selected' : '' }}>Archive</option>
                            <option value="unarchive"            {{ request('action_type') === 'unarchive'             ? 'selected' : '' }}>Unarchive</option>
                            <option value="delete"               {{ request('action_type') === 'delete'                ? 'selected' : '' }}>Delete Employee</option>
                            <option value="pds_status"           {{ request('action_type') === 'pds_status'            ? 'selected' : '' }}>PDS Status</option>
                            <option value="create_admin"         {{ request('action_type') === 'create_admin'          ? 'selected' : '' }}>Create Admin</option>
                            <option value="update_admin"         {{ request('action_type') === 'update_admin'          ? 'selected' : '' }}>Update Admin</option>
                            <option value="delete_admin"         {{ request('action_type') === 'delete_admin'          ? 'selected' : '' }}>Delete Admin</option>
                            <option value="profile_edit_approved"{{ request('action_type') === 'profile_edit_approved' ? 'selected' : '' }}>Edit Approved</option>
                            <option value="profile_edit_rejected"{{ request('action_type') === 'profile_edit_rejected' ? 'selected' : '' }}>Edit Rejected</option>
                        </select>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Admin Role</label>
                        <select name="admin_role" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">All Roles</option>
                            <option value="main admin" {{ request('admin_role') === 'main admin' ? 'selected' : '' }}>Main Admin</option>
                            <option value="admin user" {{ request('admin_role') === 'admin user' ? 'selected' : '' }}>Admin User</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            Filter
                        </button>
                        @if(request()->hasAny(['search','action_type','admin_role']))
                            <a href="{{ route('admin.activity.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- Table -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="px-4 sm:px-8 py-3 sm:py-4 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <p class="text-base sm:text-lg font-semibold text-slate-900">Activity Log</p>
                        <p class="text-xs sm:text-sm text-slate-500">{{ $mappedLogs->total() }} total {{ Str::plural('record', $mappedLogs->total()) }}</p>
                    </div>
                    <span class="text-xs sm:text-sm text-slate-500 font-semibold">Updated {{ now()->format('M d, Y') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-left text-xs sm:text-sm font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Admin</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Role</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Activity</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Employee / Target</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Unit / Office</th>
                                <th class="px-4 sm:px-6 py-2.5 sm:py-3">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-xs sm:text-sm text-slate-700">
                            @forelse ($mappedLogs as $log)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                                        <p class="font-semibold text-slate-900">{{ $log['admin_name'] }}</p>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                                        @php
                                            $roleKey    = strtolower($log['admin_role']);
                                            $roleBadge  = str_contains($roleKey, 'main')
                                                ? 'bg-indigo-100 text-indigo-700'
                                                : 'bg-slate-100 text-slate-600';
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $roleBadge }}">
                                            {{ $log['admin_role'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 max-w-sm">
                                        @php
                                            $at = $log['action_type'];
                                            $actionBadge = match($at) {
                                                'archive'               => 'bg-amber-100 text-amber-700',
                                                'unarchive'             => 'bg-teal-100 text-teal-700',
                                                'delete', 'delete_admin'=> 'bg-rose-100 text-rose-700',
                                                'update', 'update_admin'=> 'bg-sky-100 text-sky-700',
                                                'create_admin'          => 'bg-emerald-100 text-emerald-700',
                                                'pds_status'            => 'bg-purple-100 text-purple-700',
                                                'profile_edit_approved' => 'bg-green-100 text-green-700',
                                                'profile_edit_rejected' => 'bg-red-100 text-red-700',
                                                default                 => 'bg-slate-100 text-slate-600',
                                            };
                                            $actionLabel = match($at) {
                                                'archive'               => 'Archive',
                                                'unarchive'             => 'Unarchive',
                                                'delete'                => 'Delete',
                                                'delete_admin'          => 'Delete Admin',
                                                'update'                => 'Update',
                                                'update_admin'          => 'Update Admin',
                                                'create_admin'          => 'Create Admin',
                                                'pds_status'            => 'PDS Status',
                                                'profile_edit_approved' => 'Edit Approved',
                                                'profile_edit_rejected' => 'Edit Rejected',
                                                default                 => ucfirst($at),
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
                                            <span class="text-slate-400 text-xs">{{ $log['target_user_email'] }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500">
                                        {{ $log['target_user_unit'] }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-slate-500 whitespace-nowrap">
                                        {{ $log['date_time'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 sm:px-6 py-10 text-center text-slate-400 text-sm">
                                        No activity records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($mappedLogs->hasPages())
                    <div class="px-4 sm:px-8 py-4 border-t border-slate-100">
                        {{ $mappedLogs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

</x-app-layout>
