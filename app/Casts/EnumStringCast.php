<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use BackedEnum;

class EnumStringCast implements CastsAttributes
{
    protected string $enumClass;

    /**
     * Create a new cast class instance.
     */
    public function __construct(string $enumClass)
    {
        $this->enumClass = $enumClass;
    }

    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        return $value;
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        $enumClass = $this->enumClass;
        $validValues = array_map(fn($case) => $case->value, $enumClass::cases());
        
        if (!in_array($value, $validValues, true)) {
            throw new InvalidArgumentException("The value '{$value}' is not a valid case for enum {$enumClass}");
        }

        return $value;
    }
}
