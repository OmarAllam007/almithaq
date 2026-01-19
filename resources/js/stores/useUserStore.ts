import { echo } from '@laravel/echo-vue';
import { defineStore } from 'pinia';

interface OnlineUser {
    id: number;
    name: string;
}

export const usePresenceStore = defineStore('presence', {
    state: () => ({
        onlineUsers: [] as OnlineUser[],
        isInitialized: false,
    }),
    actions: {
        initializePresence() {
            // Prevent multiple initializations
            if (this.isInitialized) {
                return;
            }

            this.isInitialized = true;

            echo()
                .join('online-users')
                .here((users: OnlineUser[]) => {
                    console.log('Users here:', users);
                    this.onlineUsers = users;
                })
                .joining((user: OnlineUser) => {
                    console.log('User joining:', user);
                    if (!this.onlineUsers.find((u) => u.id === user.id)) {
                        this.onlineUsers.push(user);
                    }
                })
                .leaving((user: OnlineUser) => {
                    console.log('User leaving:', user);
                    this.onlineUsers = this.onlineUsers.filter((u) => u.id !== user.id);
                })
                .error((error: unknown) => {
                    console.error('Presence channel error:', error);
                    this.isInitialized = false;
                });
        },
        isUserOnline(userId: number): boolean {
            return this.onlineUsers.some((u) => u.id === userId);
        },
    },
});
