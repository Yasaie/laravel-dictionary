<?php

namespace Yasaie\Dictionary;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Dictionary
 *
 * @mixin \Eloquent
 */
class Dictionary extends Model
{
    public $timestamps = false;

    protected $appends = ['full_path'];

    protected $guarded = [];

    /**
     * @package context
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function context()
    {
        return $this->morphTo();
    }

    /**
     * @package getFullPathAttribute
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @return string
     */
    public function getFullPathAttribute()
    {
        return "{$this->context_type}{$this->context_id}";
    }

    public function createLocale($key, $values)
    {
        foreach ($values as $lang => $title) {
            if ($title) {
                $this->create([
                    'key' => $key,
                    'value' => $title,
                    'language_id' => $lang
                ]);
            }
        }
    }
}
