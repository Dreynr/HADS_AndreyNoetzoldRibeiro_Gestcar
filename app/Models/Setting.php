<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, string $default = ''): string
    {
        $value = static::where('key', $key)->value('value');

        // Valor gravado em branco conta como ausente. Sem isto, uma
        // configuracao salva vazia viraria 0 no `(int)` e travaria a agenda
        // inteira: `max_simple_per_period` igual a zero recusa todo mundo.
        return ($value === null || $value === '') ? $default : $value;
    }

    public static function setValue(string $key, string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
