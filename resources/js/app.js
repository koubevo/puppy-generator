// Check if we can use service workers (requires HTTPS or localhost)
const canUseServiceWorker = 'serviceWorker' in navigator &&
    (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1');

// Service Worker Registration
if (canUseServiceWorker) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('ServiceWorker registered:', registration.scope);
        } catch (error) {
            console.error('ServiceWorker registration failed:', error);
        }
    });
}

// Push Notification Subscription
window.PushManager = {
    vapidPublicKey: null,

    async init(vapidKey) {
        this.vapidPublicKey = vapidKey;
    },

    isSupported() {
        return canUseServiceWorker && 'PushManager' in window;
    },

    async subscribe() {
        if (!this.isSupported()) {
            throw new Error('Push notifications require HTTPS. Please use a secure connection.');
        }

        if (!('Notification' in window)) {
            throw new Error('This browser does not support notifications');
        }

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            throw new Error('Notification permission denied');
        }

        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
        });

        await fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(subscription)
        });

        return subscription;
    },

    async unsubscribe() {
        if (!this.isSupported()) return;

        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            await fetch('/push/unsubscribe', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ endpoint: subscription.endpoint })
            });

            await subscription.unsubscribe();
        }
    },

    async getSubscription() {
        if (!this.isSupported()) return null;

        const registration = await navigator.serviceWorker.ready;
        return registration.pushManager.getSubscription();
    },

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }
};

// Notification toggle handler
document.addEventListener('DOMContentLoaded', async () => {
    const toggleBtn = document.getElementById('notification-toggle');
    if (!toggleBtn) return;

    // Check if push is supported
    if (!window.PushManager.isSupported()) {
        toggleBtn.textContent = 'Requires HTTPS 🔒';
        toggleBtn.disabled = true;
        toggleBtn.title = 'Push notifications require a secure (HTTPS) connection';
        return;
    }

    const vapidKey = toggleBtn.dataset.vapidKey;
    if (!vapidKey) {
        toggleBtn.textContent = 'Not configured';
        toggleBtn.disabled = true;
        return;
    }

    await window.PushManager.init(vapidKey);

    const updateButtonState = async () => {
        const subscription = await window.PushManager.getSubscription();
        if (subscription) {
            toggleBtn.textContent = '🔔 Notifications On';
            toggleBtn.classList.add('subscribed');
        } else {
            toggleBtn.textContent = '🔕 Enable Notifications';
            toggleBtn.classList.remove('subscribed');
        }
    };

    await updateButtonState();

    toggleBtn.addEventListener('click', async () => {
        toggleBtn.disabled = true;
        try {
            const subscription = await window.PushManager.getSubscription();
            if (subscription) {
                await window.PushManager.unsubscribe();
            } else {
                await window.PushManager.subscribe();
            }
            await updateButtonState();
        } catch (error) {
            console.error('Push subscription error:', error);
            alert(error.message);
        } finally {
            toggleBtn.disabled = false;
        }
    });
});

// Load More handler
document.addEventListener('DOMContentLoaded', () => {
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (!loadMoreBtn) return;

    const feedContainer = document.getElementById('feed-container');
    const btnLabel = loadMoreBtn.querySelector('span');
    const btnIcon = loadMoreBtn.querySelector('svg');

    loadMoreBtn.addEventListener('click', async () => {
        const nextBefore = loadMoreBtn.dataset.nextBefore;
        if (!nextBefore) return;

        loadMoreBtn.disabled = true;
        btnLabel.textContent = 'Loading…';
        if (btnIcon) btnIcon.classList.add('hidden');

        try {
            const response = await fetch(`/feed/more?before=${nextBefore}`);
            const data = await response.json();

            feedContainer.insertAdjacentHTML('beforeend', data.html);

            if (data.hasMore && data.nextBefore) {
                loadMoreBtn.dataset.nextBefore = data.nextBefore;
                btnLabel.textContent = 'Load more';
                if (btnIcon) btnIcon.classList.remove('hidden');
                loadMoreBtn.disabled = false;
            } else {
                loadMoreBtn.closest('#load-more-wrapper').remove();
            }
        } catch (error) {
            console.error('Failed to load more:', error);
            btnLabel.textContent = 'Load more';
            if (btnIcon) btnIcon.classList.remove('hidden');
            loadMoreBtn.disabled = false;
        }
    });
});

