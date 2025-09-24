<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

// Components Reka UI
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Item, ItemType, ResourceCollection } from '@/types';

// Définition des props et emits
const props = defineProps<{
    itemTypes: ResourceCollection<ItemType>; // Types d'articles disponibles
    modelValue: boolean; // Pour contrôler l'ouverture/fermeture du dialogue (v-model:open)
    item: Item | null; // L'article à éditer
}>();

const emit = defineEmits(['update:modelValue', 'itemUpdated']);

// Formulaire d'édition d'article
const editItemForm = useForm<{
    name: string;
    quantity: number;
    unit: string;
    price_per_unit: number | undefined;
    item_type_id: number | null;
}>({
    name: '',
    quantity: 0,
    unit: '',
    price_per_unit: undefined,
    item_type_id: null,
});

// Watcher pour mettre à jour le formulaire quand l'item change (quand le dialogue s'ouvre avec un nouvel item)
watch(() => props.item, (newItem) => {
    if (newItem) {
        editItemForm.name = newItem.name;
        editItemForm.quantity = newItem.quantity;
        editItemForm.unit = newItem.unit || '';
        editItemForm.price_per_unit = newItem.price_per_unit ?? undefined;
        editItemForm.item_type_id = newItem.item_type_id;
    }
}, { immediate: true }); // Exécuter immédiatement au montage si item est déjà présent

const submitEditItem = () => {
    if (!props.item) return; // Sécurité

    editItemForm.put(route('app.inventory.update', props.item.id), {
        onSuccess: () => {
            editItemForm.reset();
            editItemForm.price_per_unit = undefined; // Réinitialiser pour le prochain ajout
            emit('update:modelValue', false); // Ferme le dialogue
            emit('itemUpdated'); // Notifie le parent qu'un article a été mis à jour
            toast.success('Article mis à jour avec succès !');
        },
        onError: (errors) => {
            console.error("Erreur lors de la mise à jour de l'article:", errors);
            toast.error('Erreur lors de la mise à jour de l\'article.');
        }
    });
};

// Gérer la fermeture du dialogue
const closeDialog = () => {
    editItemForm.reset();
    editItemForm.price_per_unit = undefined;
    emit('update:modelValue', false);
};
</script>

<template>
    <Dialog :open="modelValue" @update:open="closeDialog">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Modifier Article : {{ item?.name }}</DialogTitle>
                <DialogDescription>
                    Mettez à jour les informations de cet article.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitEditItem" class="grid gap-4 py-4" v-if="item">
                <div class="grid gap-2">
                    <Label for="edit_name">Nom de l'article</Label>
                    <Input id="edit_name" type="text" v-model="editItemForm.name" required />
                    <InputError :message="editItemForm.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_quantity">Quantité</Label>
                    <Input id="edit_quantity" type="number" v-model.number="editItemForm.quantity" required min="0" />
                    <InputError :message="editItemForm.errors.quantity" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_unit">Unité (ex: pièces, m, g)</Label>
                    <Input id="edit_unit" type="text" v-model="editItemForm.unit" />
                    <InputError :message="editItemForm.errors.unit" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_price_per_unit">Prix unitaire (achat)</Label>
                    <Input id="edit_price_per_unit" type="number" step="0.01" v-model.number="editItemForm.price_per_unit" />
                    <InputError :message="editItemForm.errors.price_per_unit" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_item_type_id">Type d'article</Label>
                    <Select v-model="editItemForm.item_type_id">
                        <SelectTrigger>
                            <SelectValue :placeholder="editItemForm.item_type_id ? itemTypes.data.find(t => t.id === editItemForm.item_type_id)?.name : 'Sélectionner un type'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem :value="null">Aucun</SelectItem>
                                <SelectItem v-for="type in itemTypes.data" :key="type.id" :value="type.id">{{ type.name }}</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="editItemForm.errors.item_type_id" />
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary" @click="closeDialog">Annuler</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="editItemForm.processing">Sauvegarder</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
