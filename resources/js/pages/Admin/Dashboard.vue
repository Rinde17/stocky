<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard Admin',
        href: '/admin/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-card overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-foreground">
                        Bienvenue sur le tableau de bord administrateur de Stocky !
                        <p class="mt-4 text-sm text-muted-foreground">
                            Ici, vous pourrez gérer les aspects globaux de la plateforme : utilisateurs, abonnements, statistiques, etc.
                            (Fonctionnalités à venir)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
