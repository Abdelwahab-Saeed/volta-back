<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Offer;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\ImageUploader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * One-off conversion of images uploaded before ImageUploader existed. Originals stay on disk and every
 * old → new path is written to a CSV in storage/logs, so the paths can be put back if needed.
 */
class ConvertImagesToWebp extends Command
{
    protected $signature = 'images:convert-webp {--dry-run : Report what would change without writing anything}';

    protected $description = 'Convert stored PNG/JPG uploads to WebP and point their rows at the new files';

    /** Every model column that holds an upload path on the public disk. */
    public const COLUMNS = [
        Product::class => 'image',
        ProductImage::class => 'image',
        Category::class => 'image',
        Banner::class => 'image',
        Post::class => 'image',
        Certificate::class => 'image',
        User::class => 'image',
        Offer::class => 'image',
        Partner::class => 'logo',
        TeamMember::class => 'photo',
    ];

    public function handle(ImageUploader $images): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $converted = $missing = $failed = $before = $after = 0;
        $log = $logPath = null;

        if (! $dryRun) {
            $logPath = storage_path('logs/webp-conversion-'.now()->format('Ymd-His').'.csv');
            $log = fopen($logPath, 'w');
            fputcsv($log, ['table', 'id', 'column', 'old_path', 'new_path']);
        }

        foreach (self::COLUMNS as $model => $column) {
            // Deleted rows can be restored, so their images are converted too.
            $model::withTrashed()
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->select(['id', $column])
                ->chunkById(200, function ($rows) use ($model, $column, $images, $disk, $dryRun, $log, &$converted, &$missing, &$failed, &$before, &$after) {
                    foreach ($rows as $row) {
                        $path = $row->{$column};
                        $table = $row->getTable();

                        if (preg_match('/\.(webp|gif|svg)$/i', $path)) {
                            continue;
                        }

                        if (! $disk->exists($path)) {
                            $missing++;
                            $this->warn("Missing file, skipped: {$table}#{$row->id} {$column} = {$path}");
                            continue;
                        }

                        $size = $disk->size($path);

                        if ($dryRun) {
                            $converted++;
                            $before += $size;
                            $this->line("Would convert {$table}#{$row->id} {$column}: {$path}");
                            continue;
                        }

                        try {
                            $newPath = $images->convertFile($path);
                        } catch (Throwable $e) {
                            $failed++;
                            $this->error("Could not convert, skipped: {$table}#{$row->id} {$column} = {$path} ({$e->getMessage()})");
                            continue;
                        }

                        if ($newPath === null) {
                            continue;
                        }

                        // A plain query: no timestamps, no model events, only this column changes.
                        $model::withTrashed()->whereKey($row->id)->toBase()->update([$column => $newPath]);
                        fputcsv($log, [$table, $row->id, $column, $path, $newPath]);

                        $converted++;
                        $before += $size;
                        $after += $disk->size($newPath);
                    }
                });
        }

        if ($dryRun) {
            $this->info("Dry run: {$converted} image(s) would be converted (".$this->mb($before).' MB now), '.$missing.' missing.');

            return self::SUCCESS;
        }

        fclose($log);
        $this->info("Converted {$converted} image(s): ".$this->mb($before).' MB → '.$this->mb($after).' MB. '.$missing.' missing, '.$failed.' failed.');
        $this->line("Old → new paths: {$logPath}. Original files were kept.");

        return self::SUCCESS;
    }

    private function mb(int $bytes): string
    {
        return number_format($bytes / 1048576, 1);
    }
}
