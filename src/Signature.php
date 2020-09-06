<?php

namespace Casperlaitw\LaravelAdmobSsv;

use Illuminate\Http\Request;

/**
 * Class Signature
 *
 * @package Casperlaitw\LaravelAdmobSsv
 */
class Signature
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \EllipticCurve\Signature
     */
    public static function createFromRequest(Request $request)
    {
        return self::create($request->input('signature'));
    }

    /**
     * @param $signature
     *
     * @return \EllipticCurve\Signature
     */
    public static function create($signature)
    {
        return \EllipticCurve\Signature::fromBase64(str_replace(['-', '_'], ['+', '/'], $signature));
    }
}
