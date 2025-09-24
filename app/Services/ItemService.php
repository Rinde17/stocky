<?php

namespace App\Services;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB; // NOUVEAU : pour DB::raw

class ItemService
{
    /**
     * Récupère tous les articles d'un utilisateur, en incluant leur type.
     */
    public function getItemsForUser(User $user): Collection
    {
        return $user->items()->with('itemType')->orderBy('name')->get();
    }

    /**
     * Crée un nouvel article pour un utilisateur.
     */
    public function createItemForUser(User $user, array $data): Item
    {
        return $user->items()->create($data);
    }

    /**
     * Met à jour un article existant.
     */
    public function updateItem(Item $item, array $data): Item
    {
        $item->update($data);
        return $item;
    }

    /**
     * Supprime un article.
     */
    public function deleteItem(Item $item): bool
    {
        return $item->delete();
    }

    // NOUVELLES MÉTHODES POUR LE DASHBOARD

    /**
     * Récupère le nombre total d'articles (lignes) pour un utilisateur.
     */
    public function getTotalItemsCount(User $user): int
    {
        return $user->items()->count();
    }

    /**
     * Récupère le nombre d'articles distincts par nom pour un utilisateur.
     */
    public function getDistinctItemsCount(User $user): int
    {
        return $user->items()->distinct('name')->count();
    }

    /**
     * Récupère la quantité totale de toutes les unités d'articles pour un utilisateur.
     */
    public function getTotalQuantity(User $user): int
    {
        return $user->items()->sum('quantity');
    }

    /**
     * Récupère le nombre d'articles en stock bas pour un utilisateur.
     */
    public function getLowStockItemsCount(User $user): int
    {
        $lowStockThreshold = $user->low_stock_threshold ?? 0;
        return $user->items()
            ->where('quantity', '<=', $lowStockThreshold)
            ->where('quantity', '>', 0)
            ->count();
    }

    /**
     * Récupère le nombre d'articles en rupture de stock pour un utilisateur.
     */
    public function getOutOfStockItemsCount(User $user): int
    {
        return $user->items()->where('quantity', 0)->count();
    }

    /**
     * Calcule la valeur totale de l'inventaire pour un utilisateur.
     */
    public function getTotalInventoryValue(User $user): float
    {
        // Assurez-vous que 'price_per_unit' existe sur votre modèle Item
        // Si vous aviez 'cost_per_unit' avant, changez le champ ici.
        return (float) $user->items()->sum(DB::raw('quantity * price_per_unit')); // OU 'price_per_unit' si c'est ce que vous utilisez
    }

    /**
     * Récupère les X articles avec le stock le plus bas pour un utilisateur.
     */
    public function getLowestStockItems(User $user, int $limit = 5): Collection
    {
        return $user->items()->with('itemType')
            ->orderBy('quantity', 'asc')
            ->take($limit)
            ->get();
    }

    /**
     * Récupère les X articles ajoutés récemment pour un utilisateur.
     */
    public function getRecentlyAddedItems(User $user, int $limit = 5): Collection
    {
        return $user->items()->with('itemType')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }
}
