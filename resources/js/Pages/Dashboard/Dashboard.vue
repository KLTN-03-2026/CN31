<script setup>
import { computed } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';

import EmployeeDashboard from './Dashboards/EmployeeDashboard.vue';


const page = usePage();
const user = computed(() => page.props.auth.user);
const props = defineProps({
    roleData: { type: Object, default: () => ({}) },
    stats: Object,
    recentRequests: Object,
    filters: Object,
    trangThais: Array
});

//ĐIỀU HƯỚNG
const dashboardComponents = {
    'nhan_vien': EmployeeDashboard,
    // 'ke_toan': AccountantDashboard, //
    // 'nhan_vien_mua_sam': PurchasingDashboard,
    // 'truong_phong': EmployeeDashboard,
    // 'giam_doc': EmployeeDashboard,
    // 'nhan_su': EmployeeDashboard,
};

// Lấy ra đúng Component dựa theo chức vụ
const CurrentDashboard = computed(() => {
    return dashboardComponents[user.value.vai_tro] || EmployeeDashboard;
});
</script>

<template>
    <Head title="Tổng quan" />

    <component
        :is="CurrentDashboard"
        :roleData="roleData"  :stats="stats"
        :recentRequests="recentRequests"
        :filters="filters"
        :trangThais="trangThais"
    />
</template>
