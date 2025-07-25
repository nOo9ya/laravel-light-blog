<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * $ p�
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * $  �
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * $ �
     */
    public static function remove(string $key): bool
    {
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * �� $ | p�
     */
    public static function getMultiple(array $keys): array
    {
        $settings = static::whereIn('key', $keys)->get();
        $result = [];
        
        foreach ($keys as $key) {
            $setting = $settings->firstWhere('key', $key);
            $result[$key] = $setting ? $setting->value : null;
        }
        
        return $result;
    }

    /**
     * �� $ |  �
     */
    public static function setMultiple(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value);
        }
    }

    /**
     * $D ��� 0| ��
     */
    public function getValueAttribute($value)
    {
        return match($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     *   �� ��� 0| ��
     */
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = match($this->type) {
            'boolean' => $value ? '1' : '0',
            'array', 'json' => json_encode($value),
            default => (string) $value,
        };
    }
}