<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import type { AdminPagination } from '@/pages/Admin/types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
interface Payment {
    id: number;
    amount: string;
    currency: string;
    status: string;
    gateway: string;
    gateway_transaction_id: string | null;
    created_at: string;
    user: { id: number; name: string; username: string; email: string } | null;
}
const props = defineProps<{ payments: AdminPagination<Payment>; status: string | null }>();
const status = ref(props.status || '');
const filter = () => router.get(route('admin.payments.index'), { status: status.value || undefined }, { preserveState: true, replace: true });
</script>
<template>
    <main class="admin-page">
        <div class="admin-shell">
            <header class="admin-hero">
                <div>
                    <small>CONTROL CENTER / BILLING</small>
                    <h1>Payments</h1>
                    <p>Gateway transactions and their recorded settlement status.</p>
                </div>
            </header>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>{{ payments.total }} transactions</h2>
                    <div class="admin-field">
                        <label for="payment-status">Status</label
                        ><select id="payment-status" v-model="status" @change="filter">
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                <div v-if="payments.data.length" class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>Member</th>
                                <th>Amount</th>
                                <th>Gateway</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in payments.data" :key="payment.id">
                                <td>
                                    <strong>#{{ payment.id }}</strong
                                    ><small>{{ payment.gateway_transaction_id || 'No gateway reference' }}</small>
                                </td>
                                <td>
                                    <strong>{{ payment.user?.name || payment.user?.username || '—' }}</strong
                                    ><small>{{ payment.user?.email }}</small>
                                </td>
                                <td>
                                    <strong>{{ payment.amount }} {{ payment.currency }}</strong>
                                </td>
                                <td>{{ payment.gateway }}</td>
                                <td>
                                    <span
                                        :class="[
                                            'admin-pill',
                                            payment.status === 'completed' ? 'good' : payment.status === 'failed' ? 'bad' : 'warn',
                                        ]"
                                        >{{ payment.status }}</span
                                    >
                                </td>
                                <td>{{ payment.created_at?.slice(0, 16) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="admin-empty">No payments found.</div>
                <div v-if="payments.last_page > 1" class="admin-panel-body"><Pagination :data="payments" /></div>
            </div>
        </div>
    </main>
</template>
