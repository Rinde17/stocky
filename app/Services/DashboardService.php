<?php

namespace App\Services;

use App\Models\User;
use App\Services\ItemService;
use App\Services\ItemTypeService;

class DashboardService
{
    protected ItemService $itemService;
    protected ItemTypeService $itemTypeService;

    public function __construct(ItemService $itemService, ItemTypeService $itemTypeService)
    {
        $this->itemService = $itemService;
        $this->itemTypeService = $itemTypeService;
    }

    /**
     * Récupère toutes les données nécessaires pour le tableau de bord d'un utilisateur.
     *
     * @param User $user
     * @return array
     */
    public function getDashboardData(User $user): array
    {
        $lowStockThreshold = $user->low_stock_threshold ?? 0;

        return [
            'stats' => [
                'totalItemsCount' => $this->itemService->getTotalItemsCount($user),
                'totalDistinctItems' => $this->itemService->getDistinctItemsCount($user),
                'totalQuantity' => $this->itemService->getTotalQuantity($user),
                'itemsInLowStock' => $this->itemService->getLowStockItemsCount($user),
                'itemsOutOfStock' => $this->itemService->getOutOfStockItemsCount($user),
                'totalItemTypes' => $this->itemTypeService->getTotalItemTypesCount($user),
                'totalInventoryValue' => $this->itemService->getTotalInventoryValue($user),
            ],
            'lowestStockItems' => $this->itemService->getLowestStockItems($user),
            'recentlyAddedItems' => $this->itemService->getRecentlyAddedItems($user),
            'lowStockThreshold' => $lowStockThreshold,
        ];
    }
}
