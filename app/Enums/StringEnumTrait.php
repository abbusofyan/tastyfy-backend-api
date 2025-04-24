<?php

namespace App\Enums;

use Illuminate\Support\Str;

trait StringEnumTrait
{
    public static function fromLabel(string $label): static
    {
        $label = strtolower($label);

        foreach (static::cases() as $case) {
            if (strtolower($case->value) === $label) { // Compare against the value property (string)
                return $case;
            }
        }

        throw new \ValueError("Invalid enum label: $label");
    }

    public static function fromSlug(string $slug): static
    {
        $slug = Str::slug($slug);
        foreach (static::cases() as $case) {
            if ($case->slug() === $slug) {
                return $case;
            }
        }
        throw new \ValueError("Invalid enum slug: $slug");
    }

    public function slug(): string
    {
        return Str::slug($this->value);
    }

    public function label(): string
    {
        return ucfirst(strtolower($this->value)); // Capitalize the first letter of the value (string)
    }
}
