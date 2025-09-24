<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { debounce } from 'lodash';

// Components Reka UI
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Item } from '@/types'; // Assurez-vous d'importer l'interface Item

// Définition des props
const props = defineProps<{
    item: Item; // L'article à afficher
    lowStockThreshold: number; // Le seuil de stock bas de l'utilisateur
}>();

const emit = defineEmits(['editItem', 'deleteItem', 'itemUpdated']); // Événements pour le parent

// État local pour la quantité pour l'Optimistic UI
const localQuantity = ref<number>(props.item.quantity);
// Stocker la quantité avant la modification pour le rollback
const originalQuantity = ref<number>(props.item.quantity);

// Watcher pour mettre à jour localQuantity si la prop item.quantity change de l'extérieur (ex: après un rechargement Inertia)
watch(() => props.item.quantity, (newQuantity) => {
    localQuantity.value = newQuantity;
    originalQuantity.value = newQuantity;
});

// Fonction pour envoyer la mise à jour de la quantité au backend
const sendQuantityUpdate = debounce(() => {
    // Si la quantité n'a pas changé, ne rien faire
    if (localQuantity.value === originalQuantity.value) {
        return;
    }

    // Préparer les données pour la requête PUT
    const dataToSend = {
        quantity: localQuantity.value,
        name: props.item.name, // Inclure pour la validation backend
        unit: props.item.unit,
        price_per_unit: props.item.price_per_unit,
        item_type_id: props.item.item_type_id,
    };

    router.put(route('app.inventory.update', props.item.id), dataToSend, {
        preserveScroll: true,
        // preserveState: true est crucial pour l'Optimistic UI,
        // car cela empêche Inertia de recharger complètement les props de la page
        // ce qui annulerait notre mise à jour locale immédiate.
        preserveState: true,
        onSuccess: () => {
            originalQuantity.value = localQuantity.value; // Mettre à jour la quantité originale après succès
            toast.success(`Quantité de "${props.item.name}" mise à jour à ${localQuantity.value}.`);
            emit('itemUpdated'); // Notifier le parent si besoin (ex: pour rafraîchir la liste si le tri est affecté)
        },
        onError: (errors) => {
            console.error("Erreur lors de la mise à jour de la quantité:", errors);
            toast.error(`Erreur lors de la mise à jour de la quantité pour "${props.item.name}".`);
            // Rollback : revenir à la quantité d'origine si la requête échoue
            localQuantity.value = originalQuantity.value;
        },
        onFinish: () => {
            // Optionnel : si vous avez un indicateur de chargement sur le bouton
        }
    });
}, 500); // Débouncé pour ne pas envoyer trop de requêtes si l'utilisateur tape rapidement

// Fonction pour incrémenter/décrémenter
const adjustQuantity = (delta: number) => {
    const newQuantity = localQuantity.value + delta;
    if (newQuantity >= 0) {
        localQuantity.value = newQuantity; // Mise à jour Optimistic UI
        sendQuantityUpdate(); // Envoi de la requête
    }
};

// Gestion de la suppression (déléguer au parent)
const confirmDeleteItem = () => {
    emit('deleteItem', props.item);
};

// Gestion de l'édition (déléguer au parent)
const openEdit = () => {
    emit('editItem', props.item);
};
</script>

<template>
    <div
        class="p-4 border rounded-lg shadow-sm bg-card"
        :class="{ 'border-destructive ring-2 ring-destructive': localQuantity <= lowStockThreshold }"
    >
        <h4 class="font-bold text-xl mb-1 text-foreground">{{ item.name }}</h4>
        <p class="text-foreground">
            Quantité :
            <Input
                type="number"
                class="w-20 text-center text-lg font-medium inline-block mx-1"
                v-model.number="localQuantity"
                min="0"
                @change="sendQuantityUpdate"
                @blur="sendQuantityUpdate"
            />
            {{ item.unit }}
        </p>
        <p v-if="item.item_type" class="text-muted-foreground text-sm">Type : {{ item.item_type.name }}</p>
        <p v-if="item.price_per_unit" class="text-muted-foreground text-sm">Prix unitaire : {{ item.price_per_unit }} €</p>
        <p class="text-muted-foreground text-xs mt-1">Dernière maj : {{ item.updated_at.split(' ')[0] }}</p>

        <div class="flex items-center space-x-2 mt-4">
            <Button variant="secondary" @click="adjustQuantity(-1)" :disabled="localQuantity <= 0" class="p-2 w-8 h-8 flex items-center justify-center">-</Button>
            <Button @click="adjustQuantity(1)" class="p-2 w-8 h-8 flex items-center justify-center">+</Button>

            <div class="flex-1 text-right">
                <Button @click="openEdit" class="text-xs px-3 py-2">Modifier</Button>
                <Button variant="destructive" @click="confirmDeleteItem" class="ml-2 text-xs px-3 py-2">Supprimer</Button>
            </div>
        </div>
    </div>
</template>
