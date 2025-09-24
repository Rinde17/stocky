<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Boxes, CircleDollarSign, Tags, Archive, TriangleAlert } from 'lucide-vue-next';

// Components Reka UI
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge'; // RÉINTRODUIT : Import du Badge

import type { BreadcrumbItem, Item } from '@/types';

// Définition des props que le contrôleur passe à la vue
const props = defineProps<{
    stats: {
        totalItemsCount: number;
        totalDistinctItems: number;
        totalQuantity: number;
        itemsInLowStock: number;
        itemsOutOfStock: number;
        totalItemTypes: number;
        totalInventoryValue: number;
    };
    lowestStockItems: Item[];
    recentlyAddedItems: Item[];
    lowStockThreshold: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tableau de Bord',
        href: '/dashboard',
    },
];

const hasLowStockOrOutOfStock = computed(() => {
    return props.stats.itemsInLowStock > 0 || props.stats.itemsOutOfStock > 0;
});

// Fonction pour déterminer la variante du badge
const getBadgeVariant = (quantity: number) => {
    if (quantity === 0) {
        return 'destructive'; // Rouge pour rupture de stock
    } else if (quantity <= props.lowStockThreshold) {
        return 'warning'; // Orange pour stock bas
    }
    return 'default'; // Couleur par défaut (ou 'secondary' si vous préférez)
};
</script>

<template>
    <Head title="Tableau de Bord" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-card overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-foreground">
                        <h2 class="text-2xl font-semibold mb-6">Aperçu de l'Inventaire</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Articles Uniques</CardTitle>
                                    <Boxes class="text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.totalDistinctItems }}</div>
                                    <p class="text-xs text-muted-foreground">Total d'articles différents.</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Quantité Totale</CardTitle>
                                    <Archive class="text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.totalQuantity }}</div>
                                    <p class="text-xs text-muted-foreground">Unités en stock.</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Types d'Articles</CardTitle>
                                    <Tags class="text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.totalItemTypes }}</div>
                                    <p class="text-xs text-muted-foreground">Catégories définies.</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Valeur Totale du Stock</CardTitle>
                                    <CircleDollarSign class="text-muted-foreground" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.totalInventoryValue.toFixed(2) }} €</div>
                                    <p class="text-xs text-muted-foreground">Basé sur le coût unitaire des articles.</p>
                                </CardContent>
                            </Card>

                            <Card v-if="hasLowStockOrOutOfStock" :class="{
                                'col-span-1 sm:col-span-2 lg:col-span-3': true,
                                'border-destructive': stats.itemsOutOfStock > 0,
                                'border-warning': stats.itemsOutOfStock === 0 && stats.itemsInLowStock > 0
                            }">
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">Alertes de Stock</CardTitle>
                                    <TriangleAlert :class="{
                                        'text-destructive': stats.itemsOutOfStock > 0,
                                        'text-warning': stats.itemsOutOfStock === 0 && stats.itemsInLowStock > 0
                                    }" />
                                </CardHeader>
                                <CardContent>
                                    <div v-if="stats.itemsOutOfStock > 0" class="text-lg font-bold text-destructive">
                                        {{ stats.itemsOutOfStock }} articles en rupture de stock !
                                    </div>
                                    <div v-if="stats.itemsInLowStock > 0" class="text-lg font-bold" :class="{
                                        'text-warning': stats.itemsOutOfStock === 0,
                                        'text-muted-foreground': stats.itemsOutOfStock > 0
                                    }">
                                        {{ stats.itemsInLowStock }} articles en stock bas (seuil: {{ lowStockThreshold }})
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-2">
                                        <Link :href="route('app.inventory.index')" class="underline text-primary">Voir tous les articles</Link> pour gérer.
                                    </p>
                                </CardContent>
                            </Card>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle class="text-lg">Articles en Stock le plus Bas</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ul v-if="lowestStockItems.length > 0" class="space-y-3">
                                        <li v-for="item in lowestStockItems" :key="item.id" class="flex justify-between items-center text-sm">
                                            <span>{{ item.name }} ({{ item.item_type?.name || 'N/A' }})</span>
                                            <Badge :variant="getBadgeVariant(item.quantity)">
                                                {{ item.quantity }}
                                            </Badge>
                                        </li>
                                    </ul>
                                    <p v-else class="text-muted-foreground text-sm">Aucun article en stock bas significatif.</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle class="text-lg">Articles Récemment Ajoutés</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ul v-if="recentlyAddedItems.length > 0" class="space-y-3">
                                        <li v-for="item in recentlyAddedItems" :key="item.id" class="flex justify-between items-center text-sm">
                                            <span>{{ item.name }} ({{ item.item_type?.name || 'N/A' }})</span>
                                            <span class="text-muted-foreground">{{ new Date(item.created_at).toLocaleDateString() }}</span>
                                        </li>
                                    </ul>
                                    <p v-else class="text-muted-foreground text-sm">Aucun article ajouté récemment.</p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
