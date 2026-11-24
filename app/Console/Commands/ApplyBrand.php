<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Models\HotelKnowledgeItem;
use App\Support\Brand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Puts the active brand (APP_BRAND) into this deployment's own database: the
 * company profile and the company knowledge items. Safe to run on every
 * deploy: items are matched by the FTS title they stand in for, which is
 * kept in details.brand_source, so a second run changes nothing.
 */
#[Signature('brand:apply {--dry-run : Show what would change without saving}')]
#[Description('Apply the APP_BRAND company profile and knowledge items to this deployment\'s database')]
class ApplyBrand extends Command
{
    public function handle(Brand $brand): int
    {
        $hotel = Hotel::where('slug', $brand->hotelSlug())->first();

        if (! $hotel) {
            $this->warn("No company with slug \"{$brand->hotelSlug()}\" yet; nothing to apply.");

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $changes = [];

        DB::transaction(function () use ($brand, $hotel, $dryRun, &$changes): void {
            $profile = $brand->profile();

            if ($profile !== []) {
                $hotel->fill($profile);

                if ($hotel->isDirty()) {
                    $changes[] = 'profile: '.implode(', ', array_keys($hotel->getDirty()));
                    $dryRun || $hotel->save();
                }
            }

            foreach ($brand->knowledge() as $source => $replacement) {
                $item = $this->findItem($hotel, $source);

                if (! $item) {
                    $changes[] = "skipped (not found): {$source}";

                    continue;
                }

                $attributes = $this->attributesFor($brand, $item, $source, $replacement);
                $item->fill($attributes);

                if ($item->isDirty()) {
                    $changes[] = ($replacement === null ? 'hidden: ' : 'updated: ').$source;
                    $dryRun || $item->save();
                }
            }
        });

        if ($changes === []) {
            $this->info("Brand \"{$brand->key()}\" is already applied.");
        } else {
            foreach ($changes as $change) {
                $this->line(($dryRun ? '[dry-run] ' : '').$change);
            }
        }

        return self::SUCCESS;
    }

    /**
     * The item that stands in for an FTS title: the one already branded from
     * it, otherwise the FTS original.
     */
    private function findItem(Hotel $hotel, string $source): ?HotelKnowledgeItem
    {
        return $hotel->knowledgeItems()->get()->first(fn (HotelKnowledgeItem $item) => ($item->details['brand_source'] ?? null) === $source)
            ?? $hotel->knowledgeItems()->where('title', $source)->first();
    }

    /**
     * @param  array<string, mixed>|null  $replacement
     * @return array<string, mixed>
     */
    private function attributesFor(Brand $brand, HotelKnowledgeItem $item, string $source, ?array $replacement): array
    {
        $details = [...($item->details ?? []), 'brand_source' => $source];

        if ($replacement === null) {
            return ['is_active' => false, 'details' => $details];
        }

        if ($replacement['rename'] ?? false) {
            $original = $item->details['brand_original'] ?? ['title' => $item->title, 'body' => $item->body, 'translations' => $item->translations];
            $swap = fn (?string $text) => $text === null ? null : str_replace('FTS', $brand->shortName(), $text);

            return [
                'title' => $swap($original['title']),
                'body' => $swap($original['body']),
                'translations' => collect($original['translations'] ?? [])->map(fn (array $copy) => array_map($swap, $copy))->all(),
                'is_active' => true,
                'details' => [...$details, 'brand_original' => $original],
            ];
        }

        $replacement = $brand->fill($replacement);
        $extraDetails = $replacement['details'] ?? [];
        unset($replacement['details']);

        return [
            ...$replacement,
            'is_active' => true,
            'details' => [...$details, ...$extraDetails],
        ];
    }
}
