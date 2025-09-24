<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemTypeResource;
use App\Models\Item;
use App\Services\ItemService;
use App\Services\ItemTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    protected ItemService $itemService;
    protected ItemTypeService $itemTypeService;

    // Injection des services via le constructeur
    public function __construct(ItemService $itemService, ItemTypeService $itemTypeService)
    {
        $this->itemService = $itemService;
        $this->itemTypeService = $itemTypeService;
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Délégation de la récupération des données aux services
        $items = $this->itemService->getItemsForUser($user);
        $itemTypes = $this->itemTypeService->getItemTypesForUser($user);

        return Inertia::render('Inventory/Index', [
            // Utilisation des Resources pour formater les collections
            'items' => ItemResource::collection($items),
            'itemTypes' => ItemTypeResource::collection($itemTypes),
            'lowStockThreshold' => $user->low_stock_threshold,
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'price_per_unit' => 'nullable|numeric|min:0',
            'item_type_id' => 'nullable|exists:item_types,id',
        ]);

        // Délégation de la création au service
        $this->itemService->createItemForUser($request->user(), $validated);

        return redirect()->route('app.inventory.index')->with('status', 'Article ajouté avec succès !');
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        // Vérification que l'utilisateur possède l'article (logique de sécurité, reste ici)
        if ($request->user()->id !== $item->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'price_per_unit' => 'nullable|numeric|min:0',
            'item_type_id' => 'nullable|exists:item_types,id',
        ]);

        // Délégation de la mise à jour au service
        $this->itemService->updateItem($item, $validated);

        return redirect()->route('app.inventory.index')->with('status', 'Article mis à jour !');
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        // Vérification de propriété
        if ($request->user()->id !== $item->user_id) {
            abort(403);
        }

        // Délégation de la suppression au service
        $this->itemService->deleteItem($item);

        return redirect()->route('app.inventory.index')->with('status', 'Article supprimé !');
    }

    public function updateLowStockThreshold(Request $request): RedirectResponse
    {
        $request->validate([
            'low_stock_threshold' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $user->low_stock_threshold = $request->input('low_stock_threshold');
        $user->save();

        return back()->with('status', 'Seuil de stock bas mis à jour avec succès.');
    }
}
