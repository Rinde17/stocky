<?php

namespace App\Services;

use App\Models\ItemType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ItemTypeService
{
    /**
     * Récupère tous les types d'articles d'un utilisateur.
     */
    public function getItemTypesForUser(User $user): Collection
    {
        return $user->itemTypes()->orderBy('name')->get();
    }

    /**
     * Crée un nouveau type d'article pour un utilisateur.
     */
    public function createItemTypeForUser(User $user, array $data): ItemType
    {
        return $user->itemTypes()->create($data);
    }

    /**
     * Met à jour un type d'article existant.
     */
    public function updateItemType(ItemType $itemType, array $data): ItemType
    {
        $itemType->update($data);
        return $itemType;
    }

    /**
     * Supprime un type d'article.
     * Note : Assurez-vous que les articles liés sont gérés (ici, la clé étrangère les met à null).
     */
    public function deleteItemType(ItemType $itemType): bool
    {
        return $itemType->delete();
    }

    // NOUVELLE MÉTHODE POUR LE DASHBOARD

    /**
     * Récupère le nombre total de types d'articles pour un utilisateur.
     */
    public function getTotalItemTypesCount(User $user): int
    {
        return $user->itemTypes()->count();
    }
}
