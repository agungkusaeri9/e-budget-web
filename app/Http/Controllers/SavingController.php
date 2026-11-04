<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingRequest;
use App\Models\Saving;
use App\Models\SavingProvider;
use App\Models\SavingTransaction;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SavingController extends Controller
{
    public function __construct(private ActivityLogService $activityLogService) {}

    public function index(Request $request)
    {
        $search = $request->string('search')->toString();

        $savings = Saving::query()
            ->with('provider:id,name,account_number,account_holder')
            ->withSum('transactions as balance', 'amount')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Saving $s) => $this->payload($s) + ['balance' => (float) $s->balance]);

        $providerSummaries = SavingProvider::query()
            ->withSum('transactions as balance', 'amount')
            ->orderBy('name')
            ->get()
            ->map(fn (SavingProvider $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'account_number' => $p->account_number,
                'account_holder' => $p->account_holder,
                'balance' => (float) $p->balance,
            ]);

        return Inertia::render('savings/Index', [
            'savings' => $savings,
            'filters' => ['search' => $search],
            'total_balance' => (float) SavingTransaction::sum('amount'),
            'provider_summaries' => $providerSummaries,
        ]);
    }

    public function create()
    {
        return Inertia::render('savings/Form', [
            'providers' => SavingProvider::orderBy('name')->get(['id', 'name', 'account_number', 'account_holder']),
        ]);
    }

    public function store(SavingRequest $request)
    {
        $saving = Saving::create($request->validated());
        $this->activityLogService->log('Create Saving', "Created saving: {$saving->name}", ['saving_id' => $saving->id]);

        return redirect()->route('savings.index')->with('success', 'Tabungan berhasil ditambahkan.');
    }

    public function show(Saving $saving)
    {
        $saving->load('provider:id,name,account_number,account_holder');

        return Inertia::render('savings/Show', [
            'saving' => $this->payload($saving) + ['balance' => (float) $saving->transactions()->sum('amount')],
            'transactions' => $saving->transactions()->latest('transaction_date')->latest('id')->get()
                ->map(fn (SavingTransaction $t) => [
                    'id' => $t->id,
                    'amount' => (float) $t->amount,
                    'transaction_date' => $t->transaction_date->toDateString(),
                    'note' => $t->note,
                    'is_manual' => $t->budget_plan_id === null,
                ]),
        ]);
    }

    public function edit(Saving $saving)
    {
        return Inertia::render('savings/Form', [
            'saving' => $this->payload($saving),
            'providers' => SavingProvider::orderBy('name')->get(['id', 'name', 'account_number', 'account_holder']),
        ]);
    }

    public function update(SavingRequest $request, Saving $saving)
    {
        $saving->update($request->validated());
        $this->activityLogService->log('Update Saving', "Updated saving: {$saving->name}", ['saving_id' => $saving->id]);

        return redirect()->route('savings.index')->with('success', 'Tabungan berhasil diperbarui.');
    }

    public function destroy(Saving $saving)
    {
        $saving->delete();
        $this->activityLogService->log('Delete Saving', "Deleted saving: {$saving->name}", ['deleted_saving_id' => $saving->id]);

        return redirect()->route('savings.index')->with('success', 'Tabungan berhasil dihapus.');
    }

    public function adjust(Request $request, Saving $saving)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0', 'between:-9999999999999,9999999999999'],
            'transaction_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $saving->transactions()->create($data);
        $this->activityLogService->log('Adjust Saving', "Adjusted saving {$saving->name}: {$data['amount']}", ['saving_id' => $saving->id] + $data);

        return back()->with('success', 'Saldo tabungan berhasil disesuaikan.');
    }

    public function destroyTransaction(Saving $saving, SavingTransaction $transaction)
    {
        abort_unless($transaction->budget_plan_id === null, 422, 'Transaksi dari budget plan tidak dapat dihapus manual.');
        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    private function payload(Saving $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'saving_provider_id' => $s->saving_provider_id,
            'provider' => $s->provider ? [
                'id' => $s->provider->id,
                'name' => $s->provider->name,
                'account_number' => $s->provider->account_number,
                'account_holder' => $s->provider->account_holder,
            ] : null,
            'target_amount' => $s->target_amount,
            'deadline' => $s->deadline?->toDateString(),
            'notes' => $s->notes,
        ];
    }
}
