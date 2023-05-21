<?php

if (!defined('\\Sodium\\CRYPTO_AUTH_BYTES')) {
    require __DIR__ . "/lib/sodium/constants.php";
}

if(!is_callable("\\Sodium\\crypto_aead_aes256gcm_is_available") && is_callable("sodium_crypto_aead_aes256gcm_is_available")) {
    require __DIR__ . "/lib/sodium/functions.php";
}