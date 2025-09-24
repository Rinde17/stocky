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
import type { ItemType } from '@/types'; // Importez l'interface ItemType

// Définition des props et emits
const props = defineProps<{
    modelValue: boolean; // Pour contrôler l'ouverture/fermeture du dialogue (v-model:open)
    itemType: ItemType | null; // Le type d'article à supprimer
}>();

const emit = defineEmits(['update:modelValue', 'itemTypeDeleted']);

// Formulaire factice pour gérer le statut de traitement (processing)
const deleteTypeForm = useForm({}); // Pas besoin de données, juste pour le statut de traitement

// Watcher pour réinitialiser le formulaire ou gérer l'état si nécessaire
watch(() => props.modelValue, (isOpen) => {
    if (!isOpen) {
        deleteTypeForm.reset(); // Réinitialise le statut de traitement quand le dialogue se ferme
    }
});

const submitDeleteType = () => {
    if (!props.itemType) {
        toast.error('Aucun type d\'article sélectionné pour la suppression.');
        return;
    }

    deleteTypeForm.delete(route('app.item_types.destroy', props.itemType.id), {
        onSuccess: () => {
            emit('update:modelValue', false); // Ferme le dialogue
            emit('itemTypeDeleted', props.itemType?.id); // Notifie le parent que le type a été supprimé
            toast.success(`Le type "${props.itemType?.name}" a été supprimé.`);
        },
        onError: (errors) => {
            console.error("Erreur lors de la suppression du type d'article:", errors);
            toast.error(`Erreur lors de la suppression du type "${props.itemType?.name}".`);
            // Ici, vous pourriez vouloir garder le dialogue ouvert ou afficher des messages d'erreur spécifiques
            // Vous pouvez aussi vérifier errors.response.data si votre backend renvoie des messages spécifiques
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
                <DialogTitle>Supprimer le type : {{ itemType?.name }}</DialogTitle>
                <DialogDescription>
                    Êtes-vous sûr de vouloir supprimer ce type d'article ?
                    <span class="font-bold text-destructive">ATTENTION :</span> Les articles associés à ce type n'auront plus de catégorie. Cette action est irréversible.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button type="button" variant="secondary" @click="closeDialog">Annuler</Button>
                </DialogClose>
                <Button type="submit" variant="destructive" :disabled="deleteTypeForm.processing" @click="submitDeleteType">
                    {{ deleteTypeForm.processing ? 'Suppression...' : 'Supprimer' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
