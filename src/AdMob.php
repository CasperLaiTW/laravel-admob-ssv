<?php

namespace Casperlaitw\LaravelAdmobSsv;

use EllipticCurve\Ecdsa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;

/**
 * Class AdMob
 *
 * @package Casperlaitw\LaravelAdmobSsv
 */
class AdMob
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * AdMob constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->configureCache();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function validate()
    {
        $this->request->validate([
            'key_id' => 'required',
            'signature' => 'required',
        ]);

        $publicKey = PublicKey::createPublicKeyFromRequest($this->request);
        $signature = Signature::createFromRequest($this->request);

        $message = collect($this->request->except(['key_id', 'signature']))
            ->map(function ($value, $key) {
                return "{$key}={$value}";
            })
            ->implode('&');

        return Ecdsa::verify($message, $signature, $publicKey);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function failed()
    {
        return !$this->validate();
    }

    /**
     * Using Laravel default cache
     */
    protected function configureCache()
    {
        PublicKey::cacheThrough(function () {
            return new CacheMiddleware(
                new PrivateCacheStrategy(
                    new LaravelCacheStorage(
                        Cache::store()
                    )
                )
            );
        });
    }
}
