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
     * @author  Payam Yasaie <payam@yasaie.ir>
     * @since   2019-08-18
     *
     * @return mixed
     */
    public function getLocales()
    {
        return $this->locales;
    }

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
     * @package getLocaleKey
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @param $name
     * @param $lang
     * @param bool $key
     *
     * @return array|string
     */
    protected function getLocaleKey($name, $lang, &$key = false)
    {
        $req = [
            $lang,
            $this->table,
            $this->id,
            $name
        ];

        $the_key = implode('.', $req);

        if ($key) {
            $key = $the_key;
            return $req;
        } else {
            return $the_key;
        }
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
        $key = true;
        $req = $this->getLocaleKey($name, $lang, $key);

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

    /**
     * @package clearLocalCache
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function clearLocalCache()
    {
        $locales = property_exists($this, 'locales')
            ? $this->locales : [];

        foreach ($locales as $locale) {
            foreach (\Config::get('global.langs') as $lang) {
                \Cache::delete($this->getLocaleKey($locale, $lang->getId()));
            }
        }
    }

    /**
     * @package deleteDictionary
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @return bool|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function deleteDictionary()
    {
        $parent = parent::delete();

        if ($parent) {
            $this->clearLocalCache();
            return $this->dictionary()->delete();
        } else {
            return false;
        }
    }

    /**
     * @package delete
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @return bool|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete()
    {
        return $this->deleteDictionary();
    }

    /**
     * @package createLocale
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @param $key
     * @param $values
     */
    public function createLocale($key, $values)
    {
        foreach ($values as $lang => $value) {
            if ($value) {
                $this->dictionary()->create([
                    'key' => $key,
                    'value' => $value,
                    'language_id' => $lang
                ]);
            }
        }
    }

    /**
     * @package updateLocale
     * @author  Payam Yasaie <payam@yasaie.ir>
     *
     * @param $key
     * @param $values
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function updateLocale($key, $values)
    {
        foreach ($values as $lang => $value) {
            if ($value) {
                $this->dictionary()->updateOrCreate([
                    'key' => $key,
                    'language_id' => $lang
                ], [
                    'value' => $value,
                ]);
            }
        }
        $this->clearLocalCache();
    }
}