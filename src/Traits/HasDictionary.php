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
    /**
     * @package __get
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @param $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->locale($key);
    }

    /**
     * @package locale
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @param $key
     *
     * @return mixed
     */
    public function locale($key)
    {
        $locales = property_exists($this, 'locales')
            ? $this->locales : [];

        if (in_array($key, $locales)) {
            return $this->getTranslate($key, \Config::get('app.locale'))
                ?: $this->getTranslate($key, \Config::get('app.fallback_locale'));
        }

        return $this->getAttribute($key);
    }

    /**
     * @package getTranslate
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @param $name
     * @param $lang
     *
     * @return mixed
     */
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

    /**
     * @package dictionary
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function dictionary()
    {
        return $this->morphMany(Dictionary::class, 'context');
    }
}