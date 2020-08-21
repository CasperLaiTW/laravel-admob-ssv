<?php

namespace Casperlaitw\LaravelAdmobSsv;

use Eastwest\Json\Json;
use Exception;
use GuzzleHttp\Client;
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
        $client = new Client();
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
        return self::createPublicKey($request->query('key_id'));
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
}
