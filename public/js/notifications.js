function notificationsComponent() {
    return {
        notifications: [],
        pollInterval: null,
        timeUpdateInterval: null,
        routes: {},
        csrfToken: '',

        init() {
            this.notifications = JSON.parse(this.$el.dataset.notifications || '[]');
            this.routes = JSON.parse(this.$el.dataset.routes || '{}');
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            this.pollInterval = setInterval(() => {
                this.fetchNotifications();
            }, 10000);

            this.timeUpdateInterval = setInterval(() => {
                this.$el.querySelectorAll('[x-text*="formatTimeAgo"]').forEach(el => {
                    this.$nextTick();
                });
            }, 10000);
        },

        async fetchNotifications() {
            try {
                const response = await fetch(this.routes.index, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications;
                }
            } catch (error) {
                console.error('Hiba az értesítések betöltésekor:', error);
            }
        },

        async markAsRead(notificationId) {
            try {
                const response = await fetch(this.routes.read.replace(':id', notificationId), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                if (response.ok) {
                    this.notifications = this.notifications.filter(n => n.id !== notificationId);
                }
            } catch (error) {
                console.error('Hiba az értesítés elolvasásakor:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch(this.routes.readAll, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                if (response.ok) {
                    this.notifications = [];
                }
            } catch (error) {
                console.error('Hiba az értesítések elolvasásakor:', error);
            }
        },

        async createMockNotification() {
            try {
                const response = await fetch(this.routes.mock, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                if (response.ok) {
                    await this.fetchNotifications();
                }
            } catch (error) {
                console.error('Hiba a mock értesítés létrehozásakor:', error);
            }
        },

        formatTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const seconds = Math.floor((now - date) / 1000);
            
            if (seconds < 5) return 'most';
            if (seconds < 60) return `${seconds} másodperce`;
            
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `${minutes} perce`;
            
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours} órája`;
            
            const days = Math.floor(hours / 24);
            if (days < 7) return `${days} napja`;
            
            const weeks = Math.floor(days / 7);
            if (weeks < 4) return `${weeks} hete`;
            
            const months = Math.floor(days / 30);
            if (months < 12) return `${months} hónapja`;
            
            const years = Math.floor(days / 365);
            return `${years} éve`;
        }
    }
}