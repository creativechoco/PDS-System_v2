<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>BFAR Region XII</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" x-data="{
        pdsModal: { show: false, status: null, latest_id: null, latest_updated_at: null },
        sessionSeed: {{ json_encode(session()->getId()) }},
        storageFor(status) {
            return status === 'approved' ? window.localStorage : window.sessionStorage;
        },
        clearDismissed(key, status) {
            if (!key) return;
            const storage = this.storageFor(status);
            storage?.removeItem(key);
        },
        keyFor(data) {
            if (!data) return null;
            const id = data.latest_id ?? null;
            const updated = data.latest_updated_at ?? null;
            const status = (data.status || '').toLowerCase();
            if (!id || !status) return null;

            // Approved: stable key per submission id, persists across logins.
            if (status === 'approved') {
                return `pds_approved_persist_${id}`;
            }

            // Rejected: session-scoped with timestamp to re-show after updates.
            if (!updated) return null;
            const seed = this.sessionSeed || 'sess';
            return `pds_rejected_${seed}_${id}_${updated}`;
        },
        isDismissed(key, status) {
            if (!key) return false;
            const storage = this.storageFor(status);
            return storage?.getItem(key) === '1';
        },
        markDismissed(key, status) {
            if (!key) return;
            const storage = this.storageFor(status);
            storage?.setItem(key, '1');
        },
        setModalFromData(detail) {
            const status = detail?.show_approved_modal
                ? 'approved'
                : (detail?.show_rejected_modal ? 'rejected' : null);
            const payload = {
                show: false,
                status,
                latest_id: detail?.latest_id ?? null,
                latest_updated_at: detail?.latest_updated_at ?? null,
            };

            const key = this.keyFor({ ...payload, status });
            const shouldShow = !!status && !this.isDismissed(key, status);

            // If approved is already dismissed locally but server still flags to show, sync dismissal to server once.
            if (status === 'approved' && detail?.show_approved_modal && key && !shouldShow) {
                fetch('{{ route('employee.approval.dismiss') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        submission_id: payload.latest_id,
                        submission_updated_at: payload.latest_updated_at,
                    }),
                }).catch(() => {});
            }

            this.pdsModal = {
                ...payload,
                show: shouldShow,
            };
        },
        dismissModal() {
            const key = this.keyFor(this.pdsModal);

            // Rejected: clear/mark only the rejected dismissal key (do not touch approved dismissal).
            if (this.pdsModal.status === 'rejected') {
                this.clearDismissed(key, 'rejected');
                this.markDismissed(key, 'rejected');
            } else {
                // Approved: mark dismissed so it only shows once until next approval event.
                this.markDismissed(key, this.pdsModal.status);
            }
            this.pdsModal.show = false;
        }
    }"
    x-init="
        const initialData = {
            show_rejected_modal: {{ !empty($pdsModalData) && ($pdsModalData['show_rejected_modal'] ?? false) ? 'true' : 'false' }},
            show_approved_modal: {{ !empty($pdsModalData) && ($pdsModalData['show_approved_modal'] ?? false) ? 'true' : 'false' }},
            latest_id: {{ $pdsModalData['latest_id'] ?? 'null' }},
            latest_updated_at: {{ $pdsModalData['latest_updated_at'] ?? 'null' }},
        };
        setModalFromData(initialData);

        window.addEventListener('pds-modal-update', (event) => {
            const detail = event.detail || {};
            setModalFromData(detail);
        });
    "
    >
        <div class="min-h-screen bg-gray-100">
            @if (Auth::check() && Auth::user()->role === 'employee')
                @include('employee_dashboard.employee_navigation')
            @else
                @include('layouts.navigation')
            @endif

            {{-- Global PDS status modal for employees (rejected / approved) --}}
            <template x-if="pdsModal.show">
                <div
                    x-cloak
                    class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
                >
                    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-slate-200 p-6">
                        <template x-if="pdsModal.status === 'rejected'">
                            <div class="space-y-2">
                                <p class="text-sm uppercase tracking-wide text-rose-600 font-semibold">PDS Rejected</p>
                                <h3 class="text-xl font-bold text-slate-900">Your Personal Data Sheet was rejected</h3>
                                <p class="text-sm text-slate-600 leading-6">
                                    Your submitted PDS has been marked as rejected by the administrator. Please review the feedback you received (if any), update your PDS, and resubmit for approval.
                                </p>
                            </div>
                        </template>

                        <template x-if="pdsModal.status === 'approved'">
                            <div class="space-y-2">
                                <p class="text-sm uppercase tracking-wide text-emerald-600 font-semibold">PDS Approved</p>
                                <h3 class="text-xl font-bold text-slate-900">Your Personal Data Sheet was approved</h3>
                                <p class="text-sm text-slate-600 leading-6">
                                  Your PDS has been approved by the administrator. You can continue to your dashboard or close this message.
                                </p>
                            </div>
                        </template>

                        <div class="mt-6 flex justify-end">
                            <form
                                x-show="pdsModal.status === 'rejected'"
                                method="POST"
                                action="{{ route('employee.rejection.dismiss') }}"
                                x-on:submit="markDismissed(keyFor(pdsModal), pdsModal.status)"
                            >
                                @csrf
                                <input type="hidden" name="submission_id" :value="pdsModal.latest_id">
                                <input type="hidden" name="submission_updated_at" :value="pdsModal.latest_updated_at">
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                                    x-on:click="dismissModal()"
                                >
                                    Close
                                </button>
                            </form>

                            <form
                                x-show="pdsModal.status === 'approved'"
                                method="POST"
                                action="{{ route('employee.approval.dismiss') }}"
                                x-on:submit="markDismissed(keyFor(pdsModal), pdsModal.status)"
                            >
                                @csrf
                                <input type="hidden" name="submission_id" :value="pdsModal.latest_id">
                                <input type="hidden" name="submission_updated_at" :value="pdsModal.latest_updated_at">
                                <div class="flex items-center gap-3">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500"
                                        x-on:click="dismissModal()"
                                    >
                                        Close
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            @stack('scripts')
        </div>
    </body>
</html>
