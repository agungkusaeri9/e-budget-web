<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingProviderRequest;
use App\Models\SavingProvider;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SavingProviderController extends Controller
{
    public function __construct(private ActivityLogService $activityLogService) {}

    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $providers = SavingProvider::query()
            ->withCount('savings')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('account_holder', 'like', "%{$search}%")
                ->orWhere('account_number', 'like', "%{$search}%")
            ))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (SavingProvider $p) => $this->payload($p) + ['savings_count' => $p->savings_count]);

        return Inertia::render('saving-providers/Index', [
            'providers' => $providers,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('saving-providers/Form');
    }

    public function store(SavingProviderRequest $request)
    {
        $provider = SavingProvider::create($request->validated());
        $this->activityLogService->log('Create Saving Provider', "Created: {$provider->name}", ['saving_provider_id' => $provider->id]);

        return redirect()->route('saving-providers.index')->with('success', 'Tempat penyimpanan berhasil ditambahkan.');
    }

    public function edit(SavingProvider $savingProvider)
    {
        return Inertia::render('saving-providers/Form', ['provider' => $this->payload($savingProvider)]);
    }

    public function update(SavingProviderRequest $request, SavingProvider $savingProvider)
    {
        $savingProvider->update($request->validated());
        $this->activityLogService->log('Update Saving Provider', "Updated: {$savingProvider->name}", ['saving_provider_id' => $savingProvider->id]);

        return redirect()->route('saving-providers.index')->with('success', 'Tempat penyimpanan berhasil diperbarui.');
    }

    public function destroy(SavingProvider $savingProvider)
    {
        $savingProvider->delete();
        $this->activityLogService->log('Delete Saving Provider', "Deleted: {$savingProvider->name}", ['deleted_saving_provider_id' => $savingProvider->id]);

        return redirect()->route('saving-providers.index')->with('success', 'Tempat penyimpanan berhasil dihapus.');
    }

    private function payload(SavingProvider $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'account_number' => $p->account_number,
            'account_holder' => $p->account_holder,
            'description' => $p->description,
        ];
    }
}
