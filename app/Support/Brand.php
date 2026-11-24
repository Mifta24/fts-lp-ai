<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * The company this deployment presents, read from config/brands/<APP_BRAND>.php.
 *
 * Copy that names the company uses ":brand" (its short name) and copy that
 * names who built the AI solutions uses ":provider" (always FTS). For the FTS
 * brand both are "FTS", so its pages read exactly as before.
 */
class Brand
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly string $key, private readonly array $config) {}

    public static function fromConfig(): self
    {
        $key = (string) config('brand.active', 'fts');
        $config = config('brands.'.$key);

        if (! is_array($config)) {
            throw new InvalidArgumentException("Unknown APP_BRAND \"{$key}\": add config/brands/{$key}.php or fix the environment.");
        }

        return new self($key, $config);
    }

    public function key(): string
    {
        return $this->key;
    }

    /** The name shown in the header, titles and the AI identity. */
    public function displayName(): string
    {
        return $this->config['display_name'];
    }

    /** The short name that replaces ":brand" in the interface copy. */
    public function shortName(): string
    {
        return $this->config['short_name'] ?? $this->displayName();
    }

    /** Who built the AI solutions and demos; replaces ":provider". */
    public function provider(): string
    {
        return $this->config['provider'] ?? 'FTS';
    }

    /** Square mark for the header tile, loader and browser tab. */
    public function logo(): string
    {
        return $this->config['logo'];
    }

    /** Full logo with the company name, for structured data. */
    public function fullLogo(): string
    {
        return $this->config['full_logo'] ?? $this->logo();
    }

    public function website(): ?string
    {
        return $this->config['website'] ?? null;
    }

    /**
     * @return array<string, string> label => url
     */
    public function social(): array
    {
        return $this->config['social'] ?? [];
    }

    /**
     * The official site and social links shown on the contact screens. Empty
     * when the brand keeps them out of the page (FTS, which never showed them).
     *
     * @return array<string, string> label => url
     */
    public function contactLinks(): array
    {
        if (! ($this->config['show_contact_links'] ?? false)) {
            return [];
        }

        return array_filter(['Website' => $this->website(), ...$this->social()]);
    }

    /** Whether the stored office hours are real and may be shown. */
    public function showsOfficeHours(): bool
    {
        return (bool) ($this->config['show_office_hours'] ?? true);
    }

    /**
     * The concierge cut-out in this brand's version (the files keep their names).
     */
    public function characterImage(string $path): string
    {
        $directory = $this->config['character_dir'] ?? 'images/character';

        return str_starts_with($path, 'images/character/')
            ? $directory.'/'.substr($path, strlen('images/character/'))
            : $path;
    }

    /** The lobby introduction under the welcome. */
    public function intro(string $locale): string
    {
        return $this->localized('intro', $locale);
    }

    /** The tagline of the opening screen. */
    public function tagline(string $locale): string
    {
        return $this->localized('tagline', $locale);
    }

    /** What kind of company the concierge works for (one English clause). */
    public function aiIdentity(): string
    {
        return $this->config['ai']['identity'];
    }

    /** Extra context for the concierge, such as who built the solutions shown. */
    public function aiContext(): string
    {
        return trim((string) ($this->config['ai']['context'] ?? ''));
    }

    /** Slug of the company record this deployment serves. */
    public function hotelSlug(): string
    {
        return $this->config['hotel_slug'] ?? 'fts';
    }

    /**
     * The company record's profile columns as this brand wants them.
     *
     * @return array<string, mixed>
     */
    public function profile(): array
    {
        return $this->config['profile'] ?? [];
    }

    /**
     * Company knowledge items to replace (array) or switch off (null), keyed
     * by the title of the FTS item they stand in for.
     *
     * @return array<string, array<string, mixed>|null>
     */
    public function knowledge(): array
    {
        return $this->config['knowledge'] ?? [];
    }

    /**
     * Puts the brand into copy that has ":brand" and ":provider" placeholders.
     *
     * @template T of array|string
     *
     * @param  T  $copy
     * @return T
     */
    public function fill(array|string $copy): array|string
    {
        if (is_array($copy)) {
            return array_map(fn ($value) => is_array($value) || is_string($value) ? $this->fill($value) : $value, $copy);
        }

        return str_replace([':brand', ':provider'], [$this->shortName(), $this->provider()], $copy);
    }

    /**
     * Interface copy this brand words differently, on top of the shared copy.
     *
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    public function copy(string $group, string $locale, array $base): array
    {
        return $this->fill([...$base, ...($this->config['copy'][$group][$locale] ?? [])]);
    }

    private function localized(string $key, string $locale): string
    {
        $values = $this->config[$key] ?? [];

        return $values[$locale] ?? $values['en'] ?? '';
    }
}
