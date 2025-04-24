<?php

namespace App\Enums;

trait DatabaseEnumTrait
{
    public static function fromLabel(string $label): static
    {
        $label = strtolower($label); // Make it case-insensitive

        foreach (static::cases() as $case) {
            if (strtolower($case->name) === $label) {
                return $case;
            }
        }

        throw new \ValueError("Invalid enum label: $label");
    }

    public function label(): string
    {
        return ucfirst(strtolower($this->name));
    }

    public function slug(): string
    {
        return strtolower($this->name);
    }
}

