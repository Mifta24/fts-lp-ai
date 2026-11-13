<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * The approved knowledge base the AI Concierge retrieves from. This is the
 * ONLY place hotel facts may come from — the model is never allowed to
 * answer a hotel-knowledge question from its own memory.
 */
#[Fillable([
    'hotel_id',
    'category',
    'title',
    'body',
    'translations',
    'tags',
    'image_url',
    'images',
    'details',
    'is_active',
    'sort_order',
])]
class HotelKnowledgeItem extends Model
{
    public const CATEGORY_GENERAL = 'general';

    public const CATEGORY_FACILITIES = 'facilities';

    public const CATEGORY_POLICIES = 'policies';

    public const CATEGORY_DINING = 'dining';

    public const CATEGORY_TRANSPORT = 'transport';

    public const CATEGORY_FAQ = 'faq';

    public const CATEGORY_AI = 'ai';

    public const CATEGORY_PORTFOLIO = 'portfolio';

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'tags' => 'array',
            'images' => 'array',
            'details' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * A short icon name for this entry, taken from its tags or title so the
     * facilities cards can show a matching symbol without another column.
     */
    public function iconName(): string
    {
        $haystack = Str::lower($this->title.' '.implode(' ', $this->tags ?? []));

        foreach ([
            'pool' => ['pool', 'swim', 'kolam'],
            'gym' => ['gym', 'fitness', 'spa', 'kebugaran'],
            'parking' => ['parking', 'parkir', 'car'],
            'dining' => ['breakfast', 'sarapan', 'restaurant', 'restoran', 'dining', 'makan'],
            'transport' => ['airport', 'bandara', 'transfer', 'shuttle', 'antar-jemput'],
            'place' => ['nearby', 'terdekat', 'attraction', 'atraksi', 'beach', 'pantai'],
            'wifi' => ['wifi', 'wi-fi', 'internet'],
        ] as $icon => $needles) {
            foreach ($needles as $needle) {
                if (Str::contains($haystack, $needle)) {
                    return $icon;
                }
            }
        }

        return 'star';
    }

    /**
     * The cover photo first, then the rest of the gallery, without repeats.
     *
     * @return list<string>
     */
    public function photoUrls(): array
    {
        return collect([$this->image_url, ...($this->images ?? [])])
            ->filter(fn ($url) => filled($url))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Opening hours as the hotel wrote them, e.g. "06:30–10:30".
     */
    public function openingHours(): ?string
    {
        $hours = $this->details['hours'] ?? null;

        return filled($hours) ? (string) $hours : null;
    }

    /**
     * The live demo this entry points to, opened in the visitor's language.
     */
    public function demoUrl(string $locale): ?string
    {
        $url = $this->details['demo_url'] ?? null;

        if (! filled($url)) {
            return null;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').'lang='.$locale;
    }

    /**
     * The AI staff role a live demo is run by (e.g. "AI Barista"), in the
     * visitor's language; null for entries that are not demos.
     */
    public function demoRole(string $locale): ?string
    {
        if ($this->demoUrl($locale) === null) {
            return null;
        }

        $role = $this->translations[$locale]['role'] ?? $this->details['role'] ?? null;

        return filled($role) ? (string) $role : null;
    }

    /**
     * The dishes or services the hotel listed for this entry. Names can be
     * translated with `translations.<locale>.menu`, a list in the same order.
     *
     * @return list<array{name: string, description: ?string, price: ?string}>
     */
    public function menuEntries(?string $locale = null): array
    {
        $names = $locale !== null ? ($this->translations[$locale]['menu'] ?? []) : [];

        return collect($this->details['menu'] ?? [])
            ->values()
            ->filter(fn ($entry) => filled($entry['name'] ?? null))
            ->map(fn ($entry, $index) => [
                'name' => filled($names[$index] ?? null) ? (string) $names[$index] : (string) $entry['name'],
                'description' => filled($entry['description'] ?? null) ? (string) $entry['description'] : null,
                'price' => filled($entry['price'] ?? null) ? (string) $entry['price'] : null,
            ])
            ->values()
            ->all();
    }

    public function translatedTitle(string $locale): string
    {
        $value = $this->translations[$locale]['title'] ?? null;

        return filled($value) ? $value : $this->title;
    }

    public function translatedBody(string $locale): string
    {
        $value = $this->translations[$locale]['body'] ?? null;

        return filled($value) ? $value : $this->body;
    }
}
