<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LabelTemplate;
use App\Models\Printer;
use App\Services\PrintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LabelStudioController extends Controller
{
    /**
     * Show the standalone Photoshop-style Label Studio workstation.
     */
    public function index(Request $request, ?int $id = null): View
    {
        Gate::authorize('viewAny', LabelTemplate::class);

        $templates = LabelTemplate::orderBy('name')->get(['id', 'name', 'width_mm', 'height_mm', 'is_active']);
        $printers = Printer::where('is_active', true)->orderBy('name')->get();

        $activeTemplate = null;
        if ($id) {
            $activeTemplate = LabelTemplate::findOrFail($id);
        } elseif (! $request->boolean('new')) {
            $activeTemplate = LabelTemplate::where('is_active', true)->orderBy('name')->first();
        }

        $defaultPrinter = $printers->firstWhere('is_default', true) ?? $printers->first();

        return view('label-studio.index', [
            'config' => [
                'csrf' => csrf_token(),
                'urls' => [
                    'save' => route('label-studio.save'),
                    'print' => route('label-studio.print'),
                    'status' => url('/label-studio/printers/__ID__/status'),
                    'studio' => url('/label-studio'),
                    'panel' => url('/label-templates'),
                    'printersAdmin' => url('/printers'),
                ],
                'template' => $activeTemplate ? [
                    'id' => $activeTemplate->id,
                    'name' => $activeTemplate->name,
                    'category' => $activeTemplate->category,
                    'width_mm' => (float) $activeTemplate->width_mm,
                    'height_mm' => (float) $activeTemplate->height_mm,
                    'gap_mm' => (float) $activeTemplate->gap_mm,
                    'direction' => (int) $activeTemplate->direction,
                    'elements' => $activeTemplate->elements,
                ] : null,
                'templates' => $templates->map(fn (LabelTemplate $t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'size' => rtrim(rtrim((string) $t->width_mm, '0'), '.') . '×' . rtrim(rtrim((string) $t->height_mm, '0'), '.') . 'mm',
                ])->values(),
                'printers' => $printers->map(fn (Printer $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->values(),
                'defaultPrinterId' => $defaultPrinter?->id,
                'assets' => [
                    ['name' => 'Seven Series Symbol', 'url' => asset('images/symbol.png')],
                    ['name' => 'Seven Series Logo', 'url' => asset('images/logo.png')],
                    ['name' => 'Logo Banner', 'url' => asset('images/logo-banner.png')],
                ],
            ],
        ]);
    }

    /**
     * Save or update a label template from the studio.
     */
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'width_mm' => 'required|numeric|min:10|max:108',
            'height_mm' => 'required|numeric|min:10|max:300',
            'gap_mm' => 'required|numeric|min:0|max:20',
            'direction' => 'required|integer|in:0,1',
            'elements' => 'required|array',
        ]);

        $template = ! empty($validated['id']) ? LabelTemplate::find($validated['id']) : null;

        Gate::authorize($template ? 'update' : 'create', $template ?? LabelTemplate::class);

        $template ??= new LabelTemplate();
        $template->fill([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'width_mm' => $validated['width_mm'],
            'height_mm' => $validated['height_mm'],
            'gap_mm' => $validated['gap_mm'],
            'direction' => $validated['direction'],
            'elements' => $validated['elements'],
            'command_language' => 'tspl',
        ]);
        if (! $template->exists) {
            $template->is_active = true;
        }
        $template->save();

        return response()->json([
            'success' => true,
            'message' => "Template '{$template->name}' saved.",
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'size' => rtrim(rtrim((string) $template->width_mm, '0'), '.') . '×' . rtrim(rtrim((string) $template->height_mm, '0'), '.') . 'mm',
            ],
        ]);
    }

    /**
     * WYSIWYG print: the studio rasterises the label at the printer's native
     * 203 DPI resolution (8 dots/mm) as a 1-bit image, which is converted into
     * a TSPL BITMAP job so the sticker matches the preview dot-for-dot.
     */
    public function print(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', LabelTemplate::class);

        $validated = $request->validate([
            'printer_id' => 'required|integer',
            'copies' => 'required|integer|min:1|max:500',
            'width_mm' => 'required|numeric|min:10|max:108',
            'height_mm' => 'required|numeric|min:10|max:300',
            'gap_mm' => 'nullable|numeric|min:0|max:20',
            'direction' => 'nullable|integer|in:0,1',
            'image_data' => 'required|string',
        ]);

        $printer = Printer::find($validated['printer_id']);
        if (! $printer) {
            return response()->json(['success' => false, 'message' => 'Selected printer was not found.'], 404);
        }

        try {
            $rawCommands = PrintService::convertImageToTspl(
                $validated['image_data'],
                (float) $validated['width_mm'],
                (float) $validated['height_mm'],
                (float) ($validated['gap_mm'] ?? 2.0),
                (int) ($validated['direction'] ?? 1),
                (int) $validated['copies']
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Could not convert the label image: ' . $e->getMessage()], 422);
        }

        return response()->json(PrintService::sendRawPrint($printer, $rawCommands));
    }

    /**
     * Live connection status for the printer selector.
     */
    public function printerStatus(Printer $printer): JsonResponse
    {
        return response()->json($printer->getStatus());
    }
}
