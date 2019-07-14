<?php
/**
 * @package     shop
 * @author      Payam Yasaie <payam@yasaie.ir>
 * @copyright   2019-06-15
 */

namespace Yasaie\Dictionary\Traits;

use Yasaie\Dictionary\Dictionary;

/**
 * App\Product
 *
 * @mixin \Eloquent
 */
trait HasDictionary
{
    public function __get($key)
    {
        return $this->locale($key);
    }

    public function locale($key)
    {
        $dictionary = property_exists($this, 'dictionary')
            ? $this->dictionary : [];

        if (in_array($key, $dictionary)) {
            return $this->getTranslate($key, \Config::get('app.locale'))
                ?: $this->getTranslate($key, \Config::get('app.fallback_locale'));
        }

        return $this->getAttribute($key);
    }

    public function getTranslate($name, $lang)
    {
        $req = [
            $lang,
            $this->table,
            $this->id,
            $name
        ];
        $key = implode('.', $req);

        return \Cache::rememberForever($key, function() use ($req) {
            return $this->dictionary()->where([
                ['language_id', $req[0]],
                ['key', $req[3]]
            ])->value('value');
        });
    }

    public function dictionary()
    {
        return $this->morphMany(Dictionary::class, 'context');
    }
}