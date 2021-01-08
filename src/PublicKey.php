<?php

namespace Casperlaitw\LaravelAdmobSsv;

use Eastwest\Json\Json;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Class PublicKey
 *
 * @package Casperlaitw\LaravelAdmobSsv
 */
class PublicKey
{
    /**
     * @var string
     */
    private $publicKeyUrl = 'https://www.gstatic.com/admob/reward/verifier-keys.json';
    /**
     * @var
     */
    private $keyId;

    /**
     * @var array
     */
    private $keyMap = [];

    /**
     * @var
     */
    public static $cacheThroughCallback;

    /**
     * PublicKey constructor.
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        if ($id) {
            $this->setKeyId($id);
        }
        $this->fetchPublicKeys();
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setKeyId($id)
    {
        $this->keyId = (int)$id;

        return $this;
    }

    /**
     *
     */
    public function fetchPublicKeys()
    {
        $client = new Client($this->buildOptions());
        $response = $client->request('GET', $this->publicKeyUrl);

        return $this->keyMap = Json::decode($response->getBody()->getContents(), true);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \EllipticCurve\PublicKey
     * @throws \Exception
     */
    public static function createPublicKeyFromRequest(Request $request)
    {
        return self::createPublicKey($request->input('key_id'));
    }

    /**
     * @param $keyId
     *
     * @return \EllipticCurve\PublicKey
     * @throws \Exception
     */
    public static function createPublicKey($keyId)
    {
        $key = new self($keyId);
        return $key->getKey();
    }

    /**
     * @return \EllipticCurve\PublicKey
     * @throws \Exception
     */
    public function getKey()
    {
        if (!$this->keyId) {
            throw new InvalidArgumentException('Missing key id');
        }

        $collection = new Collection(Arr::get($this->keyMap, 'keys', []));
        if ($key = $collection->where('keyId', $this->keyId)->first()) {
            return \EllipticCurve\PublicKey::fromPem(Arr::get($key, 'pem'));
        }

        throw new Exception('Missing public key');
    }

    /**
     * Register a callback that is for caching the response
     *
     * @param callable $callback
     */
    public static function cacheThrough(callable $callback)
    {
        static::$cacheThroughCallback = $callback;
    }

    /**
     * @return ?HandlerStack
     */
    protected function buildCacheMiddlewareStack()
    {
        if (static::$cacheThroughCallback) {
            return tap(HandlerStack::create(), function (HandlerStack $stack) {
                $stack->push(call_user_func(static::$cacheThroughCallback), 'cache');
            });
        }

        return null;
    }

    /**
     * Build guzzle client options
     * @return array
     */
    private function buildOptions()
    {
        $options = [];
        if ($handler = $this->buildCacheMiddlewareStack()) {
            $options['handler'] = $handler;
        }

        return $options;
    }
}
