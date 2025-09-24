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

const props = defineProps<{
    modelValue: boolean; // Pour contrôler l'ouverture/fermeture (v-model)
}>();

const emit = defineEmits(['update:modelValue', 'itemTypeAdded']);

const addTypeForm = useForm<{
    name: string;
    description: string;
}>({
    name: '',
    description: '',
});

// Watcher pour réinitialiser le formulaire quand le dialogue s'ouvre
watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        addTypeForm.reset();
        addTypeForm.clearErrors();
    }
});

const submitAddType = () => {
    addTypeForm.post(route('app.item_types.store'), {
        onSuccess: () => {
            emit('update:modelValue', false); // Ferme le dialogue
            emit('itemTypeAdded'); // Notifie le parent pour rafraîchir ou recharger
            toast.success('Type d\'article ajouté avec succès !');
        },
        onError: (errors) => {
            console.error("Erreur lors de l'ajout du type:", errors);
            toast.error('Erreur lors de l\'ajout du type d\'article.');
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
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Ajouter un nouveau type d'article</DialogTitle>
                <DialogDescription>
                    Définissez une nouvelle catégorie pour vos articles.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitAddType" class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="add_type_name">Nom du type</Label>
                    <Input id="add_type_name" type="text" v-model="addTypeForm.name" required autofocus />
                    <InputError :message="addTypeForm.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="add_type_description">Description (optionnel)</Label>
                    <Textarea id="add_type_description" v-model="addTypeForm.description" />
                    <InputError :message="addTypeForm.errors.description" />
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary" @click="closeDialog" :disabled="addTypeForm.processing">Annuler</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="addTypeForm.processing">Ajouter le type</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
