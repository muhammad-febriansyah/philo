<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Printer;
use App\Services\ThermalPrinterService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;
use Throwable;

class PrinterController extends Controller
{
    public function __construct(private readonly ThermalPrinterService $printer) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Cabang user only sees their own branch's printers (no global).
        // Admin sees everything; can filter by branch ('global' => null branch_id).
        $branchFilter = null;
        $filterMode = 'all';
        if ($user->isCabang()) {
            $branchFilter = $user->branch_id;
            $filterMode = 'branch';
        } elseif ($request->filled('branch')) {
            if ($request->string('branch')->toString() === 'global') {
                $filterMode = 'global';
            } else {
                $branchFilter = $request->integer('branch');
                $filterMode = 'branch';
            }
        }

        $printers = Printer::query()
            ->with('branch')
            ->when($filterMode === 'branch', fn ($q) => $q->where('branch_id', $branchFilter))
            ->when($filterMode === 'global', fn ($q) => $q->whereNull('branch_id'))
            ->orderByRaw('branch_id IS NULL DESC')
            ->orderBy('branch_id')
            ->orderBy('purpose')
            ->get();

        return view('printers.index', [
            'printers' => $printers,
            'branches' => $this->visibleBranches($request),
            'branchFilter' => $request->string('branch')->toString(),
            'purposeLabels' => Printer::purposeLabels(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('printers.create', $this->formData($request) + [
            'printer' => new Printer(['profile' => 'simple', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        $this->ensureBranchAllowed($request, (int) $data['branch_id']);

        Printer::create($data);

        return redirect()->route('printers.index')
            ->with('success', 'Printer berhasil ditambahkan.');
    }

    public function edit(Request $request, Printer $printer): View
    {
        $this->ensureBranchAllowed($request, $printer->branch_id);

        return view('printers.edit', $this->formData($request) + [
            'printer' => $printer,
        ]);
    }

    public function update(Request $request, Printer $printer): RedirectResponse
    {
        $this->ensureBranchAllowed($request, $printer->branch_id);

        $data = $this->validatePayload($request, $printer);
        $this->ensureBranchAllowed($request, (int) $data['branch_id']);

        $printer->update($data);

        return redirect()->route('printers.index')
            ->with('success', 'Printer berhasil diperbarui.');
    }

    public function destroy(Request $request, Printer $printer): RedirectResponse
    {
        $this->ensureBranchAllowed($request, $printer->branch_id);

        $printer->delete();

        return redirect()->route('printers.index')
            ->with('success', 'Printer berhasil dihapus.');
    }

    public function test(Request $request, Printer $printer): JsonResponse
    {
        $this->ensureBranchAllowed($request, $printer->branch_id);

        try {
            $this->printer->printTest($printer);

            return response()->json([
                'message' => 'Test print terkirim. Cek output thermal printer.',
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function scan(): JsonResponse
    {
        return response()->json($this->detectDevices());
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        return [
            'branches' => $this->visibleBranches($request),
            'purposes' => Printer::purposeLabels(),
            'connectors' => Printer::connectorLabels(),
            'profiles' => Printer::profileOptions(),
            'devices' => $this->detectDevices(),
        ];
    }

    /**
     * @return Collection<int, Branch>
     */
    private function visibleBranches(Request $request): Collection
    {
        $query = Branch::query()->where('is_active', true)->orderBy('name');

        if ($request->user()->isCabang()) {
            $query->where('id', $request->user()->branch_id);
        }

        return $query->get(['id', 'name', 'code']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?Printer $existing = null): array
    {
        $purposes = array_keys(Printer::purposeLabels());
        $connectors = array_keys(Printer::connectorLabels());
        $branchIds = Branch::pluck('id')->all();

        $rules = [
            'branch_id' => ['nullable', 'integer', 'in:'.implode(',', $branchIds)],
            'purpose' => ['required', 'string', 'in:'.implode(',', $purposes)],
            'name' => ['nullable', 'string', 'max:100'],
            'connector' => ['required', 'string', 'in:'.implode(',', $connectors)],
            'device' => ['required', 'string', 'max:255'],
            'profile' => ['nullable', 'string', 'max:64'],
            'is_active' => ['nullable', 'boolean'],
        ];

        $data = $request->validate($rules);
        $data['branch_id'] = $data['branch_id'] ?? null;
        $data['profile'] = $data['profile'] ?? 'simple';
        $data['is_active'] = $request->boolean('is_active');

        $duplicate = Printer::query()
            ->where(function ($q) use ($data) {
                $data['branch_id'] === null
                    ? $q->whereNull('branch_id')
                    : $q->where('branch_id', $data['branch_id']);
            })
            ->where('purpose', $data['purpose'])
            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
            ->exists();

        if ($duplicate) {
            $where = $data['branch_id'] === null ? 'global' : 'cabang ini';
            abort(422, "Sudah ada printer {$where} untuk purpose tersebut. Edit yang sudah ada.");
        }

        return $data;
    }

    private function ensureBranchAllowed(Request $request, ?int $branchId): void
    {
        $user = $request->user();
        if (! $user->isCabang()) {
            return;
        }

        // Cabang user can only manage printers for their own branch (not global, not other branches).
        if ($branchId !== $user->branch_id) {
            abort(403, 'Anda hanya boleh mengelola printer cabang sendiri.');
        }
    }

    /**
     * @return array{serial: array<int, string>, cups: array<int, string>}
     */
    private function detectDevices(): array
    {
        return [
            'serial' => $this->scanSerialDevices(),
            'cups' => $this->scanCupsPrinters(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function scanSerialDevices(): array
    {
        $devices = [];
        foreach (glob('/dev/cu.*') ?: [] as $path) {
            $devices[] = $path;
        }
        foreach (glob('/dev/tty.*') ?: [] as $path) {
            $devices[] = $path;
        }
        foreach (glob('/dev/usb/lp*') ?: [] as $path) {
            $devices[] = $path;
        }
        foreach (glob('/dev/ttyUSB*') ?: [] as $path) {
            $devices[] = $path;
        }
        sort($devices);

        return array_values(array_unique($devices));
    }

    /**
     * @return array<int, string>
     */
    private function scanCupsPrinters(): array
    {
        try {
            $result = Process::timeout(3)->run('lpstat -p');
            if (! $result->successful()) {
                return [];
            }
        } catch (Throwable) {
            return [];
        }

        $printers = [];
        foreach (preg_split('/\R/', $result->output()) ?: [] as $line) {
            if (preg_match('/^printer\s+(\S+)/i', $line, $matches) === 1) {
                $printers[] = $matches[1];
            }
        }

        return $printers;
    }
}
