<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendNtfyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:task {message : The notification message to send} {--title=Task Completed : Title of the notification} {--tags=white_check_mark : Tags/emojis for the notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a push notification to ntfy.sh topic upon task completion';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $topic = env('NTFY_TOPIC', 'ntfy-7sdkamkaj-notify-8f92b7');
        $message = $this->argument('message');
        $title = $this->option('title');
        $tags = $this->option('tags');

        $url = "https://ntfy.sh/{$topic}";

        try {
            $response = Http::withHeaders([
                'Title' => $title,
                'Tags' => $tags,
                'Priority' => 'default',
            ])->post($url, $message);

            if ($response->successful()) {
                $this->info("Notification sent successfully to ntfy topic: {$topic}");

                return Command::SUCCESS;
            }

            $this->error('Failed to send notification: '.$response->body());

            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Error sending notification: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
