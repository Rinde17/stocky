<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemTypeResource;
use App\Models\ItemType;
use App\Services\ItemTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Import de la Resource ItemType
// Import du Service ItemType

class ItemTypeController extends Controller
{
    protected ItemTypeService $itemTypeService;

    public function __construct(ItemTypeService $itemTypeService)
    {
        $this->itemTypeService = $itemTypeService;
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $itemTypes = $this->itemTypeService->getItemTypesForUser($user);

        return Inertia::render('ItemType/Index', [
            'itemTypes' => ItemTypeResource::collection($itemTypes),
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:item_types,name,NULL,id,user_id,'.auth()->id(), // Unique par utilisateur
            'description' => 'nullable|string',
        ]);

        $this->itemTypeService->createItemTypeForUser($request->user(), $validated);

        return redirect()->route('app.item_types.index')->with('status', 'Type d\'article créé avec succès !');
    }

    public function update(Request $request, ItemType $itemType): RedirectResponse
    {
        if ($request->user()->id !== $itemType->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:item_types,name,'.$itemType->id.',id,user_id,'.auth()->id(), // Unique par utilisateur, ignorer l'ID actuel
            'description' => 'nullable|string',
        ]);

        $this->itemTypeService->updateItemType($itemType, $validated);

        return redirect()->route('app.item_types.index')->with('status', 'Type d\'article mis à jour !');
    }

    public function destroy(Request $request, ItemType $itemType): RedirectResponse
    {
        if ($request->user()->id !== $itemType->user_id) {
            abort(403);
        }

        $this->itemTypeService->deleteItemType($itemType);

        return redirect()->route('app.item_types.index')->with('status', 'Type d\'article supprimé !');
    }
}
