<?php

namespace Discord;

use RuntimeException;

class Interaction {
  public static function verifyKey($rawBody, $signature, $timestamp, $client_public_key) {
    if (! class_exists('\Elliptic\EdDSA')) {
      throw new RuntimeException('The `simplito/elliptic-php` package is required to validate interactions.');
    }

    $ec = new \Elliptic\EdDSA('ed25519');
    $key = $ec->keyFromPublic($client_public_key, 'hex');

    $message = array_merge(unpack('C*', $timestamp), unpack('C*', $rawBody));
    return $key->verify($message, $signature) == TRUE;
  }
}
