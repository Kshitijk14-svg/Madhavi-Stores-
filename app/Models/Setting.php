<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if ($setting) {
                $decoded = json_decode($setting->value, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
            }
            return $default;
        });
    }

    public static function set($key, $value)
    {
        $encoded = is_array($value) || is_object($value) ? json_encode($value) : $value;
        $result = self::updateOrCreate(['key' => $key], ['value' => $encoded]);
        \Illuminate\Support\Facades\Cache::forget('setting_' . $key);
        return $result;
    }

    /**
     * Parse rich typography formats (like *emphasis*, **bold**, newlines) securely 
     * for non-technical admins without needing raw HTML inputs.
     *
     * @param string|null $text
     * @return string
     */
    public static function format($text)
    {
        if (empty($text)) {
            return '';
        }

        // Convert literal '\n' and '\\n' strings to actual newline characters
        $text = str_replace(['\n', '\\n'], "\n", $text);

        // 1. Escape HTML for security
        $text = e($text);

        // 2. Parse bold: **text** -> <strong>text</strong>
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);

        // 3. Parse italic: *text* -> <em>$1</em>
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

        // 4. Parse italic: _text_ -> <em>$1</em>
        $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);

        // 5. Convert newlines to breaks
        $text = nl2br($text);

        return $text;
    }
}
