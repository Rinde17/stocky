<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3'; // Import router pour handleTypeDeleted
import { ref } from 'vue';

// Components Reka UI (seulement ceux utilisés directement dans cette page)
import { Button } from '@/components/ui/button';

import type { BreadcrumbItem, ItemType, ResourceCollection } from '@/types';

// Imports des dialogues
import AddItemTypeDialog from '@/pages/ItemType/AddItemTypeDialog.vue'; // Correction du chemin
import EditItemTypeDialog from '@/pages/ItemType/EditItemTypeDialog.vue'; // Correction du chemin
import ConfirmDeleteItemTypeDialog from '@/pages/ItemType/ConfirmDeleteItemTypeDialog.vue'; // Correction du chemin

import ItemTypeCard from '@/pages/ItemType/ItemTypeCard.vue'; // Assurez-vous du chemin correct


defineProps<{
    itemTypes: ResourceCollection<ItemType>;
}>();

// --- Variables pour le dialogue d'ajout ---
const showAddDialog = ref<boolean>(false);
const handleItemTypeAdded = () => {
    // La prop itemTypes sera automatiquement mise à jour par Inertia
    // et le toast est géré dans AddItemTypeDialog
    router.reload({ only: ['itemTypes'] }); // Explicitement pour la cohérence
};

// --- Variables pour le dialogue d'édition ---
const showEditDialog = ref<boolean>(false);
const editingType = ref<ItemType | null>(null);

const openEditDialog = (type: ItemType) => { // Reçoit le type de ItemTypeCard
    editingType.value = type;
    showEditDialog.value = true;
};
const handleItemTypeUpdated = () => {
    // La prop itemTypes sera automatiquement mise à jour par Inertia
    // et le toast est géré dans EditItemTypeDialog
    router.reload({ only: ['itemTypes'] }); // Explicitement pour la cohérence
};


// --- Gestion de la suppression ---
const showConfirmDeleteTypeDialog = ref<boolean>(false);
const typeToDelete = ref<ItemType | null>(null);

const handleDeleteType = (type: ItemType) => { // Reçoit le type de ItemTypeCard
    typeToDelete.value = type;
    showConfirmDeleteTypeDialog.value = true;
};

const handleTypeDeleted = () => {
    // Le dialogue de suppression a déjà affiché le toast de succès
    router.reload({ only: ['itemTypes'] }); // Explicitement pour la cohérence
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes Catégories',
        href: '/app/item-types',
    },
];
</script>

<template>
    <Head title="Mes Catégories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-card overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-foreground">

                        <Button @click="showAddDialog = true" class="mb-6">Ajouter un nouveau type</Button>
                        <AddItemTypeDialog
                            v-model:modelValue="showAddDialog"
                            @itemTypeAdded="handleItemTypeAdded"
                        />

                        <EditItemTypeDialog
                            v-model:modelValue="showEditDialog"
                            :itemType="editingType"
                            @itemTypeUpdated="handleItemTypeUpdated"
                        />

                        <ConfirmDeleteItemTypeDialog
                            v-model:modelValue="showConfirmDeleteTypeDialog"
                            :itemType="typeToDelete"
                            @itemTypeDeleted="handleTypeDeleted"
                        />

                        <hr class="my-6 border-border">

                        <h3 class="text-lg font-medium text-foreground mb-4">Mes Types d'Articles</h3>
                        <div v-if="itemTypes.data.length === 0" class="text-muted-foreground">
                            Vous n'avez pas encore de types d'articles. Ajoutez-en un ci-dessus !
                        </div>
                        <ul v-else class="space-y-4">
                            <ItemTypeCard
                                v-for="type in itemTypes.data"
                                :key="type.id"
                                :itemType="type"
                                @editType="openEditDialog"
                                @deleteType="handleDeleteType"
                            />
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
