<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
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
import type { ItemType, ResourceCollection } from '@/types';

// Définition des props et emits
defineProps<{
    itemTypes: ResourceCollection<ItemType>; // Types d'articles disponibles
    modelValue: boolean; // Pour contrôler l'ouverture/fermeture du dialogue (v-model:open)
}>();

const emit = defineEmits(['update:modelValue', 'itemAdded']); // Émet un événement quand un article est ajouté

// Formulaire d'ajout d'article
const addItemForm = useForm<{
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

// Fonction pour soumettre le formulaire (débouncée)
const submitAddItem = debounce(() => {
    addItemForm.post(route('app.inventory.store'), {
        onSuccess: () => {
            addItemForm.reset();
            addItemForm.price_per_unit = undefined; // Réinitialiser pour le prochain ajout
            emit('update:modelValue', false); // Ferme le dialogue
            emit('itemAdded'); // Notifie le parent qu'un article a été ajouté
            toast.success('Article ajouté avec succès !');
        },
        onError: (errors) => {
            console.error("Erreur lors de l'ajout de l'article:", errors);
            toast.error('Erreur lors de l\'ajout de l\'article.');
        }
    });
}, 300);

// Gérer la fermeture du dialogue
const closeDialog = () => {
    addItemForm.reset();
    addItemForm.price_per_unit = undefined;
    emit('update:modelValue', false);
};
</script>

<template>
    <Dialog :open="modelValue" @update:open="closeDialog">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Ajouter un nouvel article</DialogTitle>
                <DialogDescription>
                    Remplissez les informations ci-dessous pour ajouter un article à votre inventaire.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitAddItem" class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="add_name">Nom de l'article</Label>
                    <Input id="add_name" type="text" v-model="addItemForm.name" required autofocus />
                    <InputError :message="addItemForm.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="add_quantity">Quantité</Label>
                    <Input id="add_quantity" type="number" v-model.number="addItemForm.quantity" required min="0" />
                    <InputError :message="addItemForm.errors.quantity" />
                </div>
                <div class="grid gap-2">
                    <Label for="add_unit">Unité (ex: pièces, m, g)</Label>
                    <Input id="add_unit" type="text" v-model="addItemForm.unit" />
                    <InputError :message="addItemForm.errors.unit" />
                </div>
                <div class="grid gap-2">
                    <Label for="add_price_per_unit">Prix unitaire (achat)</Label>
                    <Input id="add_price_per_unit" type="number" step="0.01" v-model.number="addItemForm.price_per_unit" />
                    <InputError :message="addItemForm.errors.price_per_unit" />
                </div>
                <div class="grid gap-2">
                    <Label for="add_item_type_id">Type d'article</Label>
                    <Select v-model="addItemForm.item_type_id">
                        <SelectTrigger>
                            <SelectValue :placeholder="addItemForm.item_type_id ? itemTypes.data.find(t => t.id === addItemForm.item_type_id)?.name : 'Sélectionner un type'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem :value="null">Aucun</SelectItem>
                                <SelectItem v-for="type in itemTypes.data" :key="type.id" :value="type.id">{{ type.name }}</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="addItemForm.errors.item_type_id" />
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary" @click="closeDialog">Annuler</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="addItemForm.processing">Ajouter l'article</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
