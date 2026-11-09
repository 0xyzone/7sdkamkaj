<?php

namespace App\Livewire;

use App\Services\PrintService;
use Filament\Notifications\Notification;
use Livewire\Component;

class PrintQueueIndicator extends Component
{
    public bool $isOpen = false;
    public array $jobs = [];
    public int $jobCount = 0;

    public function mount(): void
    {
        $this->refreshQueue();
    }

    public function refreshQueue(): void
    {
        $this->jobs = PrintService::getPrintQueue();
        $this->jobCount = count($this->jobs);
    }

    public function openModal(): void
    {
        $this->refreshQueue();
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    public function cancelJob(string $jobId): void
    {
        $res = PrintService::cancelPrintJob($jobId);
        $this->refreshQueue();

        if ($res['success']) {
            Notification::make()
                ->title('Job Cancelled')
                ->body($res['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Cancel Failed')
                ->body($res['message'])
                ->danger()
                ->send();
        }
    }

    public function cancelAllJobs(): void
    {
        $res = PrintService::cancelPrintJob(all: true);
        $this->refreshQueue();

        if ($res['success']) {
            Notification::make()
                ->title('Print Queue Cleared')
                ->body($res['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error')
                ->body($res['message'])
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.print-queue-indicator');
    }
}
