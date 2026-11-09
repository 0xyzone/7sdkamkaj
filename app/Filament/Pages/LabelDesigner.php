<?php

namespace App\Filament\Pages;

use App\Models\LabelTemplate;
use App\Models\Printer;
use App\Services\PrintService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LabelDesigner extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static string|\UnitEnum|null $navigationGroup = 'Printing & Labels';

    protected static ?string $navigationLabel = 'Label Studio';

    protected static ?string $title = 'Thermal Label Studio';

    protected static ?string $slug = 'label-studio/{record?}';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.label-designer';

    // State properties
    public ?int $templateId = null;
    public string $name = 'Garment Tag Design';
    public string $category = 'garment';
    public float $width_mm = 50.00;
    public float $height_mm = 30.00;
    public float $gap_mm = 2.00;
    public int $direction = 1;
    public array $elements = [];
    public ?int $selectedPrinterId = null;
    public int $copies = 1;
    public string $sampleBrand = 'SEVEN SERIES';
    public string $sampleItemName = 'Authentic Denim Jeans';
    public string $sampleSize = '32';
    public string $samplePrice = 'Rs. 2,999';
    public string $sampleBarcode = '7S-DENIM-32-001';

    public function mount(?int $record = null): void
    {
        $defaultPrinter = Printer::where('is_default', true)->first() ?? Printer::first();
        $this->selectedPrinterId = $defaultPrinter?->id;

        if ($record) {
            $template = LabelTemplate::findOrFail($record);
            $this->loadTemplate($template);
            return;
        }

        // Load the first template if available, or create a default set of elements
        $template = LabelTemplate::first();
        if ($template) {
            $this->loadTemplate($template);
        } else {
            $this->resetToDefaultTemplate();
        }
    }

    public function loadTemplate(LabelTemplate $template): void
    {
        $this->templateId = $template->id;
        $this->name = $template->name;
        $this->category = $template->category ?? 'garment';
        $this->width_mm = (float) $template->width_mm;
        $this->height_mm = (float) $template->height_mm;
        $this->gap_mm = (float) ($template->gap_mm ?? 2.00);
        $this->direction = (int) ($template->direction ?? 1);
        $this->elements = is_array($template->elements) ? $template->elements : [];
    }

    public function selectTemplate(int $id): void
    {
        $template = LabelTemplate::findOrFail($id);
        $this->loadTemplate($template);
        $this->dispatch('elements-reloaded', ['elements' => $this->elements]);
        Notification::make()->title("Loaded template: {$this->name}")->info()->send();
    }

    public function resetToDefaultTemplate(): void
    {
        $this->templateId = null;
        $this->name = 'New Garment Tag';
        $this->category = 'garment';
        $this->width_mm = 50.00;
        $this->height_mm = 30.00;
        $this->gap_mm = 2.00;
        $this->direction = 1;
        $this->elements = [
            ['id' => 't1', 'type' => 'text', 'x' => 20, 'y' => 12, 'content' => 'SEVEN SERIES', 'fontSize' => 16, 'bold' => true],
            ['id' => 'l1', 'type' => 'line', 'x' => 20, 'y' => 38, 'width' => 220, 'height' => 2],
            ['id' => 't2', 'type' => 'text', 'x' => 20, 'y' => 45, 'content' => 'Article: {{item_name}}', 'fontSize' => 12, 'bold' => false],
            ['id' => 't3', 'type' => 'text', 'x' => 20, 'y' => 65, 'content' => 'Size: {{size}}', 'fontSize' => 14, 'bold' => true],
            ['id' => 't4', 'type' => 'text', 'x' => 140, 'y' => 65, 'content' => 'MRP: {{price}}', 'fontSize' => 14, 'bold' => true],
            ['id' => 'b1', 'type' => 'barcode', 'x' => 20, 'y' => 95, 'content' => '{{barcode}}', 'height' => 38],
        ];
        $this->dispatch('elements-reloaded', ['elements' => $this->elements]);
    }

    public function setPreset(string $preset): void
    {
        match ($preset) {
            '50x30' => [$this->width_mm = 50.00, $this->height_mm = 30.00],
            '50x70' => [$this->width_mm = 50.00, $this->height_mm = 70.00],
            '40x30' => [$this->width_mm = 40.00, $this->height_mm = 30.00],
            '60x40' => [$this->width_mm = 60.00, $this->height_mm = 40.00],
            '80x50' => [$this->width_mm = 80.00, $this->height_mm = 50.00],
            '100x150' => [$this->width_mm = 100.00, $this->height_mm = 150.00],
            default => null,
        };
    }

    public function saveTemplate(string $elementsJson): void
    {
        $decoded = json_decode($elementsJson, true);
        if (is_array($decoded)) {
            $this->elements = $decoded;
        }

        // Constraint check max nozzle size
        if ($this->width_mm > 108.00) {
            $this->width_mm = 108.00;
        }

        $template = $this->templateId
            ? LabelTemplate::find($this->templateId)
            : new LabelTemplate();

        if (! $template) {
            $template = new LabelTemplate();
        }

        $template->name = $this->name;
        $template->category = $this->category;
        $template->width_mm = $this->width_mm;
        $template->height_mm = $this->height_mm;
        $template->gap_mm = $this->gap_mm;
        $template->direction = $this->direction;
        $template->elements = $this->elements;
        $template->save();

        $this->templateId = $template->id;

        Notification::make()
            ->title('Template Saved Successfully')
            ->body("'{$template->name}' ({$template->width_mm}mm × {$template->height_mm}mm) saved to studio.")
            ->success()
            ->send();
    }

    public function printDirect(string $elementsJson): void
    {
        $decoded = json_decode($elementsJson, true);
        if (is_array($decoded)) {
            $this->elements = $decoded;
        }

        $printer = Printer::find($this->selectedPrinterId);
        if (! $printer) {
            Notification::make()->title('Printer Not Found')->body('Please select a valid destination printer.')->danger()->send();
            return;
        }

        // Create temporary template instance to compile TSPL
        $template = new LabelTemplate([
            'width_mm' => $this->width_mm,
            'height_mm' => $this->height_mm,
            'gap_mm' => $this->gap_mm,
            'direction' => $this->direction,
            'elements' => $this->elements,
        ]);

        $sampleData = [
            'brand' => $this->sampleBrand,
            'item_name' => $this->sampleItemName,
            'size' => $this->sampleSize,
            'price' => $this->samplePrice,
            'barcode' => $this->sampleBarcode,
        ];

        $tspl = $template->compileTspl($sampleData, max(1, $this->copies));
        $result = PrintService::sendRawPrint($printer, $tspl);

        if ($result['success']) {
            Notification::make()
                ->title('Printed Successfully')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Print Dispatch Failed')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function getAvailableTemplates(): \Illuminate\Database\Eloquent\Collection
    {
        return LabelTemplate::where('is_active', true)->orderBy('name')->get();
    }

    public function getAvailablePrinters(): \Illuminate\Database\Eloquent\Collection
    {
        return Printer::where('is_active', true)->orderBy('name')->get();
    }
}

