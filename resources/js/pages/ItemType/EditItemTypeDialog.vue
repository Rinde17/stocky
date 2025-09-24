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
import { Textarea } from '@/components/ui/textarea';
import type { ItemType } from '@/types'; // Importez l'interface ItemType

const props = defineProps<{
    modelValue: boolean; // Pour contrôler l'ouverture/fermeture (v-model)
    itemType: ItemType | null; // Le type d'article à éditer
}>();

const emit = defineEmits(['update:modelValue', 'itemTypeUpdated']);

const editTypeForm = useForm<{
    name: string;
    description: string;
}>({
    name: '',
    description: '',
});

// Watcher pour initialiser le formulaire quand le dialogue s'ouvre
watch(() => props.modelValue, (isOpen) => {
    if (isOpen && props.itemType) {
        editTypeForm.name = props.itemType.name;
        editTypeForm.description = props.itemType.description || '';
        editTypeForm.clearErrors(); // Important pour effacer les erreurs précédentes
    } else if (!isOpen) {
        editTypeForm.reset(); // Réinitialiser le formulaire à la fermeture
    }
}, { immediate: true }); // Exécuter immédiatement si le dialogue est déjà ouvert au montage

const submitEditType = () => {
    if (!props.itemType) {
        toast.error('Aucun type d\'article sélectionné pour l\'édition.');
        return;
    }
    editTypeForm.put(route('app.item_types.update', props.itemType.id), {
        onSuccess: () => {
            emit('update:modelValue', false); // Ferme le dialogue
            emit('itemTypeUpdated', props.itemType?.id); // Notifie le parent
            toast.success('Type d\'article mis à jour avec succès !');
        },
        onError: (errors) => {
            console.error("Erreur lors de la mise à jour du type:", errors);
            toast.error('Erreur lors de la mise à jour du type d\'article.');
        }
    });
};

// Gérer la fermeture du dialogue via le bouton Annuler ou le clic extérieur
const closeDialog = () => {
    emit('update:modelValue', false);
};
</script>

<template>
    <Dialog :open="modelValue" @update:open="closeDialog">
        <DialogContent class="sm:max-w-[425px]" v-if="itemType">
            <DialogHeader>
                <DialogTitle>Modifier Type : {{ itemType.name }}</DialogTitle>
                <DialogDescription>
                    Mettez à jour les informations de ce type d'article.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitEditType" class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="edit_type_name">Nom du type</Label>
                    <Input id="edit_type_name" type="text" v-model="editTypeForm.name" required />
                    <InputError :message="editTypeForm.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_type_description">Description (optionnel)</Label>
                    <Textarea id="edit_type_description" v-model="editTypeForm.description" />
                    <InputError :message="editTypeForm.errors.description" />
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary" @click="closeDialog" :disabled="editTypeForm.processing">Annuler</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="editTypeForm.processing">Sauvegarder</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
