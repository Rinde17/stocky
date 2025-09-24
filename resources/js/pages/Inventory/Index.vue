<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { debounce } from 'lodash';

// Components Reka UI
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

import type { BreadcrumbItem, Item, ItemType, ResourceCollection } from '@/types';
import { toast } from 'vue-sonner';

// Vos composants modulaires
import AddItemDialog from '@/pages/Inventory/AddItemDialog.vue';
import EditItemDialog from '@/pages/Inventory/EditItemDialog.vue';
import ConfirmDeleteItemDialog from '@/pages/Inventory/ConfirmDeleteItemDialog.vue'; // NOUVEAU : Import du dialogue de suppression
import ItemCard from '@/pages/Inventory/ItemCard.vue';

const props = defineProps<{
    items: ResourceCollection<Item>;
    itemTypes: ResourceCollection<ItemType>;
    lowStockThreshold: number;
}>();

// --- Modals/Dialogues d'ajout et d'édition ---
const showAddDialog = ref<boolean>(false);
const showEditDialog = ref<boolean>(false);
const editingItem = ref<Item | null>(null);

// NOUVEAU : Variables pour le dialogue de suppression
const showConfirmDeleteDialog = ref<boolean>(false);
const itemToDelete = ref<Item | null>(null);


const handleAddItem = () => {
    router.reload({ only: ['items'] });
};

const handleEditItem = (item: Item) => {
    editingItem.value = item;
    showEditDialog.value = true;
};

// NOUVEAU : Fonction pour déclencher le dialogue de suppression
const handleDeleteItem = (item: Item) => {
    itemToDelete.value = item;
    showConfirmDeleteDialog.value = true;
};

// NOUVEAU : Fonction appelée après la suppression réussie du dialogue de suppression
const handleItemDeleted = () => {
    // Le dialogue de suppression a déjà affiché le toast de succès
    router.reload({ only: ['items'] }); // Recharger les items après suppression
};


// --- Gestion du Seuil de Stock ---
const lowStockForm = useForm<{
    low_stock_threshold: number;
}>({
    low_stock_threshold: props.lowStockThreshold,
});

const updateLowStockThreshold = debounce(() => {
    lowStockForm.patch(route('app.user.updateLowStockThreshold'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Seuil de stock bas mis à jour !');
        },
        onError: (errors) => {
            console.error("Erreur lors de la mise à jour du seuil:", errors);
            toast.error('Erreur lors de la mise à jour du seuil de stock.');
        }
    });
}, 500);

// --- Recherche et Tri ---
const search = ref<string>('');
const selectedItemType = ref<number | null>(null);
const sortQuantityOrder = ref<'asc' | 'desc' | null>(null);

const filteredAndSortedItems = computed(() => {
    const items = props.items.data.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(search.value.toLowerCase());
        const matchesType = selectedItemType.value ? (item.item_type_id === selectedItemType.value) : true;
        return matchesSearch && matchesType;
    });

    if (sortQuantityOrder.value) {
        items.sort((a, b) => {
            if (sortQuantityOrder.value === 'asc') {
                return a.quantity - b.quantity;
            } else {
                return b.quantity - a.quantity;
            }
        });
    }

    return items;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mon Inventaire',
        href: '/app/inventory',
    },
];
</script>

<template>
    <Head title="Mon Inventaire" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-card overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-foreground">

                        <!-- Bouton et Dialogue d'ajout d'article -->
                        <Button @click="showAddDialog = true" class="mb-6">Ajouter un nouvel article</Button>
                        <AddItemDialog
                            v-model:modelValue="showAddDialog"
                            :itemTypes="itemTypes"
                            @itemAdded="handleAddItem"
                        />

                        <!-- Dialogue d'édition d'article -->
                        <EditItemDialog
                            v-model:modelValue="showEditDialog"
                            :item="editingItem"
                            :itemTypes="itemTypes"
                            @itemUpdated="router.reload({ only: ['items'] });"
                        />

                        <!-- NOUVEAU : Dialogue de confirmation de suppression -->
                        <ConfirmDeleteItemDialog
                            v-model:modelValue="showConfirmDeleteDialog"
                            :item="itemToDelete"
                            @itemDeleted="handleItemDeleted"
                        />

                        <hr class="my-6 border-border">

                        <!-- Configuration du Seuil de Stock Bas -->
                        <div class="mb-6 p-4 border border-border rounded-lg bg-background">
                            <h4 class="font-bold text-lg mb-2">Configuration du Seuil de Stock Bas</h4>
                            <form @submit.prevent="updateLowStockThreshold" class="flex flex-col sm:flex-row items-end sm:items-center gap-4">
                                <div class="grid gap-2 flex-1">
                                    <Label for="low_stock_threshold">Quantité seuil pour "stock bas"</Label>
                                    <Input
                                        id="low_stock_threshold"
                                        type="number"
                                        v-model.number="lowStockForm.low_stock_threshold"
                                        min="0"
                                        required
                                        @blur="updateLowStockThreshold"
                                    />
                                    <InputError :message="lowStockForm.errors.low_stock_threshold" />
                                </div>
                                <Button type="submit" :disabled="lowStockForm.processing">
                                    {{ lowStockForm.processing ? 'Sauvegarde...' : 'Sauvegarder le seuil' }}
                                </Button>
                            </form>
                        </div>

                        <hr class="my-6 border-border">

                        <!-- Recherche et Tri -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            <div class="grid gap-2">
                                <Label for="search">Rechercher par nom</Label>
                                <Input id="search" type="text" v-model="search" placeholder="Nom de l'article..." />
                            </div>
                            <div class="grid gap-2">
                                <Label for="filter_item_type">Filtrer par type</Label>
                                <Select v-model="selectedItemType">
                                    <SelectTrigger id="filter_item_type">
                                        <SelectValue :placeholder="selectedItemType ? itemTypes.data.find(t => t.id === selectedItemType)?.name : 'Tous les types'" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem :value="null">Aucun</SelectItem>
                                            <SelectItem v-for="type in itemTypes.data" :key="type.id" :value="type.id">{{ type.name }}</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label for="sort_quantity">Trier par quantité</Label>
                                <Select v-model="sortQuantityOrder">
                                    <SelectTrigger id="sort_quantity">
                                        <SelectValue :placeholder="sortQuantityOrder === 'asc' ? 'Quantité : Croissant' : (sortQuantityOrder === 'desc' ? 'Quantité : Décroissant' : 'Ordre par défaut')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem :value="null">Ordre par défaut</SelectItem>
                                            <SelectItem value="asc">Quantité : Croissant</SelectItem>
                                            <SelectItem value="desc">Quantité : Décroissant</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Liste des articles (utilise ItemCard) -->
                        <h3 class="text-lg font-medium text-foreground mb-4">Mes Articles en Stock</h3>
                        <div v-if="filteredAndSortedItems.length === 0" class="text-muted-foreground">
                            Votre inventaire est vide ou aucun article ne correspond à votre recherche.
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <ItemCard
                                v-for="item in filteredAndSortedItems"
                                :key="item.id"
                                :item="item"
                                :lowStockThreshold="lowStockThreshold"
                                @editItem="handleEditItem"
                                @deleteItem="handleDeleteItem"
                                @itemUpdated="router.reload({ only: ['items'] });"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
