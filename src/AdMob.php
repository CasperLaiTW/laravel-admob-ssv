<?php

namespace Casperlaitw\LaravelAdmobSsv;

use EllipticCurve\Ecdsa;
use Illuminate\Http\Request;

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
        $message = $this->request->except(['key_id', 'signature']);

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
}
