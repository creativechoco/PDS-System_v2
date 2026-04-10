import './bootstrap';

import Alpine from 'alpinejs';
import './register-camera';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('dashboardPreview', (submissions = []) => ({
        submissions,
        modalOpen: false,
        selected: null,
        init() {
            window.addEventListener('pds-status-updated', (e) => {
                const detail = e.detail || {};
                const id = detail.id;
                if (id == null) return;

                const idx = this.submissions.findIndex(s => s.id === id);
                if (idx !== -1) {
                    this.submissions.splice(idx, 1, {
                        ...this.submissions[idx],
                        status: detail.status,
                        status_key: detail.status_key,
                    });
                }

                if (this.selected?.id === id) {
                    this.selected = {
                        ...this.selected,
                        status: detail.status,
                        status_key: detail.status_key,
                    };
                }
            });
        },
        openById(id, fallback = null) {
            const numericId = Number(id);
            const latest = this.submissions.find(s => Number(s.id) === numericId);
            const chosen = latest || fallback;
            if (!chosen) return;
            this.open(chosen);
        },
        open(submission) {
            this.selected = { ...submission };
            this.modalOpen = true;
        },
        close() {
            this.modalOpen = false;
            this.selected = null;
        },
        requestConfirm(newStatus) {
            this.setStatus(newStatus);
        },
        downloadPds() {
            if (this.selected?.user_id) {
                window.open(`/pds-preview/${this.selected.user_id}/download?ts=${Date.now()}`, '_blank');
            }
        },
        async setStatus(newStatus) {
            if (!this.selected?.id) {
                console.error('No submission selected');
                return;
            }

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
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '',
                    },
                    body: JSON.stringify({ status: statusLabel }),
                });

                if (!response.ok) throw new Error('Failed to update status');

                const data = await response.json();

                this.selected = {
                    ...this.selected,
                    status: data.submission.status,
                    status_key: data.submission.status_key,
                };

                const index = this.submissions.findIndex(s => s.id === this.selected.id);
                if (index !== -1) {
                    this.submissions.splice(index, 1, { ...this.selected });
                }

                window.dispatchEvent(new CustomEvent('pds-status-updated', {
                    detail: {
                        id: this.selected.id,
                        status: data.submission.status,
                        status_key: data.submission.status_key,
                    },
                }));
            } catch (error) {
                console.error(error);
                alert('Failed to update status.');
            }
        },
        normalized(value) {
            return (value ?? '').toString().toLowerCase();
        },


        
    }));
});

Alpine.start();
