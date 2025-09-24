<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

// Components Reka UI
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Item } from '@/types'; // Importez l'interface Item

// Définition des props et emits
const props = defineProps<{
    modelValue: boolean; // Pour contrôler l'ouverture/fermeture du dialogue (v-model:open)
    item: Item | null; // L'article à supprimer
}>();

const emit = defineEmits(['update:modelValue', 'itemDeleted']);

// Formulaire factice pour gérer le statut de traitement (processing)
const deleteForm = useForm({}); // Pas besoin de données, juste pour le statut de traitement

// Watcher pour réinitialiser le formulaire ou gérer l'état si nécessaire
watch(() => props.modelValue, (isOpen) => {
    if (!isOpen) {
        deleteForm.reset(); // Réinitialise le statut de traitement quand le dialogue se ferme
    }
});

const submitDelete = () => {
    if (!props.item) {
        toast.error('Aucun article sélectionné pour la suppression.');
        return;
    }

    deleteForm.delete(route('app.inventory.destroy', props.item.id), {
        onSuccess: () => {
            emit('update:modelValue', false); // Ferme le dialogue
            emit('itemDeleted', props.item?.id); // Notifie le parent que l'article a été supprimé
            toast.success(`L'article "${props.item?.name}" a été supprimé.`);
        },
        onError: (errors) => {
            console.error("Erreur lors de la suppression de l'article:", errors);
            toast.error(`Erreur lors de la suppression de l'article "${props.item?.name}".`);
            // Ici, vous pourriez vouloir garder le dialogue ouvert ou afficher des messages d'erreur spécifiques
        }
    });
};

// Gérer la fermeture du dialogue
const closeDialog = () => {
    emit('update:modelValue', false);
};
</script>

<template>
    <Dialog :open="modelValue" @update:open="closeDialog">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Supprimer l'article : {{ item?.name }}</DialogTitle>
                <DialogDescription>
                    Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.
                    Toutes les données associées à cet article seront définitivement supprimées.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button type="button" variant="secondary" @click="closeDialog">Annuler</Button>
                </DialogClose>
                <Button type="submit" variant="destructive" :disabled="deleteForm.processing" @click="submitDelete">
                    {{ deleteForm.processing ? 'Suppression...' : 'Supprimer' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
