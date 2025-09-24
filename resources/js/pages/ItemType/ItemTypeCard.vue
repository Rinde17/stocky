<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { ItemType } from '@/types';

const props = defineProps<{
    itemType: ItemType;
}>();

const emit = defineEmits(['editType', 'deleteType']); // Émet les événements

const onEditClick = () => {
    emit('editType', props.itemType);
};

const onDeleteClick = () => {
    emit('deleteType', props.itemType);
};
</script>

<template>
    <li class="p-4 border border-border rounded-lg shadow-sm flex justify-between items-center bg-card">
        <div>
            <p class="font-semibold text-lg text-foreground">{{ itemType.name }}</p>
            <p v-if="itemType.description" class="text-muted-foreground text-sm">{{ itemType.description }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <Button @click="onEditClick" class="text-xs px-3 py-2">Modifier</Button>
            <Button variant="destructive" @click="onDeleteClick" class="text-xs px-3 py-2">Supprimer</Button>
        </div>
    </li>
</template>
