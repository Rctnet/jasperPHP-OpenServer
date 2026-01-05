const MainRoutes = {
    path: '/',
    meta: {
        requiresAuth: true
    },
    redirect: '/',
    component: () => import('@/layouts/full/FullLayout.vue'),
    children: [
        {
            name: 'Dashboard',
            path: '/',
            component: () => import('@/views/dashboard/index.vue')
        },
        {
            name: 'Reports',
            path: '/reports',
            component: () => import('@/views/pages/Reports.vue')
        },
        {
            name: 'Data Sources',
            path: '/data-sources',
            component: () => import('@/views/pages/DataSources.vue')
        },
        {
            name: 'Execute Report',
            path: '/execute-report',
            component: () => import('@/views/pages/ExecuteReport.vue')
        },
        {
            name: 'My Account',
            path: '/my-account',
            component: () => import('@/views/pages/MyAccount.vue')
        }
    ]
};

export default MainRoutes;
