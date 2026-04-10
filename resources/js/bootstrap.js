import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const isSecure = window.location.protocol === 'https:';
const reverbConfig = {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'localkey',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || (isSecure ? 443 : 6001),
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: isSecure,
    enabledTransports: isSecure ? ['wss'] : ['ws', 'wss'],
};

window.Echo = new Echo(reverbConfig);

const badgeEl = () => document.getElementById('notification-badge');
const listEl = () => document.getElementById('notification-list');

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}

async function markNotificationAsRead(notificationId, triggerEl, { keepalive = false, skipDom = false } = {}) {
    if (!notificationId) return;
    const token = getCsrfToken();
    try {
        const res = await fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                'X-Requested-With': 'XMLHttpRequest',
            },
            keepalive,
        });
        if (!res.ok) throw new Error(`Failed with status ${res.status}`);
        if (!skipDom) {
            if (triggerEl) {
                triggerEl.remove();
            }
            decrementBadge();
            const item = triggerEl?.closest?.('[data-notification-id]');
            if (item) {
                item.classList.remove('bg-indigo-50');
                item.classList.add('bg-white');
            }
        }
        return true;
    } catch (e) {
        console.error('Failed to mark notification as read', e);
        return false;
    }
}

window.markNotificationAsRead = markNotificationAsRead;

function decrementBadge() {
    const el = badgeEl();
    if (!el) return;
    const current = Math.max(Number(el.dataset.count || 0) - 1, 0);
    el.dataset.count = current;
    el.textContent = current;
    if (current === 0) {
        el.classList.add('hidden');
    }
}

window.viewNotification = function (event, id, link) {
    if (event?.preventDefault) event.preventDefault();
    if (id) {
        // Fire-and-forget mark as read; navigation happens immediately.
        markNotificationAsRead(id, event?.target, { keepalive: true });
    }
    if (link) {
        window.location.href = link;
    }
    return false;
};

function incrementBadge() {
    const el = badgeEl();
    if (!el) return;
    const current = Number(el.dataset.count || 0) + 1;
    el.dataset.count = current;
    el.textContent = current;
    el.classList.remove('hidden');
}

function prependNotification({ id, title, message, link }) {
    const container = listEl();
    if (!container) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'px-6 py-5 bg-indigo-50 hover:bg-indigo-50/70 transition';
    if (id) wrapper.setAttribute('data-notification-id', id);
    wrapper.innerHTML = `
        <div class="flex items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="text-sm font-semibold text-gray-900 leading-snug">${title || 'Notification'}</div>
                    <span class="text-[11px] text-gray-400 shrink-0">Just now</span>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <div class="text-xs text-gray-600 mt-1 leading-relaxed">${message || ''}</div>
                    <div class="mt-3 flex items-center gap-4 text-xs font-semibold">
                        ${link ? `<a href="${link}" onclick="return viewNotification(event,'${id || ''}','${link || ''}')" class="text-indigo-600 hover:text-indigo-800">View</a>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;

    container.prepend(wrapper);
}

async function refreshNotifications() {
    try {
        const res = await fetch('/notifications/latest', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });
        if (!res.ok) throw new Error(`Status ${res.status}`);
        const data = await res.json();
        const { unread_count = 0, notifications = [] } = data || {};

        const badge = badgeEl();
        if (badge) {
            badge.dataset.count = unread_count;
            badge.textContent = unread_count;
            if (unread_count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        const container = listEl();
        if (container) {
            container.innerHTML = '';
            notifications.forEach((n) => {
                const wrapper = document.createElement('div');
                wrapper.className = `px-6 py-5 ${n.read_at ? 'bg-white' : 'bg-indigo-50'} hover:bg-indigo-50/70 transition`;
                if (n.id) wrapper.setAttribute('data-notification-id', n.id);
                wrapper.innerHTML = `
                    <div class="flex items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div class="text-sm font-semibold text-gray-900 leading-snug">${n.title || 'Notification'}</div>
                                <span class="text-[11px] text-gray-400 shrink-0">${n.created_at_human || ''}</span>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="text-xs text-gray-600 mt-1 leading-relaxed">${n.message || ''}</div>
                                <div class="mt-3 flex items-center gap-4 text-xs font-semibold">
                                    ${n.link ? `<a href="${n.link}" onclick="return viewNotification(event,'${n.id || ''}','${n.link || ''}')" class="text-indigo-600 hover:text-indigo-800">View</a>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(wrapper);
            });
            if (!notifications.length) {
                container.innerHTML = '<div class="px-6 py-8 text-sm text-gray-500 text-center">No notifications yet.</div>';
            }
        }
    } catch (e) {
        console.warn('Failed to refresh notifications', e);
    }
}

function initNotificationChannel() {
    if (window.currentAdminId && window.Echo) {
        window.Echo.private(`App.Models.AdminUser.${window.currentAdminId}`)
            .notification((notification) => {
                incrementBadge();
                prependNotification(notification);
                refreshNotifications();

                if ((notification?.kind || '').toLowerCase() === 'pds_submitted') {
                    refreshAdminSubmissions();
                }
            });
    }

    if (window.currentUserId && window.Echo) {
        window.Echo.private(`App.Models.User.${window.currentUserId}`)
            .notification((notification) => {
                incrementBadge();
                prependNotification(notification);
                refreshNotifications();

                const status = (notification?.status || '').toLowerCase();
                const latestId = notification?.submission_id || null;
                const latestUpdatedAt = notification?.updated_at_ts || null;

                if (status === 'rejected' || status === 'approved') {
                    // Instant modal from payload
                    window.dispatchEvent(new CustomEvent('pds-modal-update', {
                        detail: {
                            show_rejected_modal: status === 'rejected',
                            show_approved_modal: status === 'approved',
                            latest_id: latestId,
                            latest_updated_at: latestUpdatedAt,
                        },
                    }));
                }
            });
    }
}

function initNotificationPolling() {
    if (!window.currentUserId && !window.currentAdminId) return;
    refreshNotifications();
}

async function refreshAdminSubmissions() {
    try {
        const res = await fetch('/pds-form/latest', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });
        if (!res.ok) throw new Error(`Status ${res.status}`);
        const submissions = await res.json();
        window.dispatchEvent(new CustomEvent('pds-submissions-update', { detail: { submissions } }));
    } catch (e) {
        console.warn('Failed to refresh admin submissions', e);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotificationChannel);
} else {
    initNotificationChannel();
}
