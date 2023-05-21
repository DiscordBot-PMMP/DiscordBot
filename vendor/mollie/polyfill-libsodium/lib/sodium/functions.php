<?php

namespace Sodium;

/**
 * Can you access AES-256-GCM? This is only available if you have supported
 * hardware.
 *
 * @return bool
 */
function crypto_aead_aes256gcm_is_available(): bool
{
    return \sodium_crypto_aead_aes256gcm_is_available();
}

/**
 * Authenticated Encryption with Associated Data (decrypt)
 * AES-256-GCM
 *
 * @param string $msg encrypted message
 * @param string $nonce
 * @param string $key
 * @param string $ad additional data (optional)
 * @return string
 */
function crypto_aead_aes256gcm_decrypt(
    string $msg,
    string $nonce,
    string $key,
    string $ad = ''
): string
{
    return \sodium_crypto_aead_aes256gcm_decrypt($msg, $nonce, $key, $ad);
}

/**
 * Authenticated Encryption with Associated Data (encrypt)
 * AES-256-GCM
 *
 * @param string $msg plaintext message
 * @param string $nonce
 * @param string $key
 * @param string $ad additional data (optional)
 * @return string
 */
function crypto_aead_aes256gcm_encrypt(
    string $msg,
    string $nonce,
    string $key,
    string $ad = ''
): string
{
    return \sodium_crypto_aead_aes256gcm_encrypt($msg, $nonce, $key, $ad);
}

/**
 * Authenticated Encryption with Associated Data (decrypt)
 * ChaCha20 + Poly1305
 *
 * @param string $msg encrypted message
 * @param string $nonce
 * @param string $key
 * @param string $ad additional data (optional)
 * @return string
 */
function crypto_aead_chacha20poly1305_decrypt(
    string $msg,
    string $nonce,
    string $key,
    string $ad = ''
): string
{
    return \sodium_crypto_aead_chacha20poly1305_decrypt($msg, $nonce, $key, $ad);
}

/**
 * Authenticated Encryption with Associated Data (encrypt)
 * ChaCha20 + Poly1305
 *
 * @param string $msg plaintext message
 * @param string $nonce
 * @param string $key
 * @param string $ad additional data (optional)
 * @return string
 */
function crypto_aead_chacha20poly1305_encrypt(
    string $msg,
    string $nonce,
    string $key,
    string $ad = ''
): string
{
    return \sodium_crypto_aead_chacha20poly1305_encrypt($msg, $nonce, $key, $ad);
}

/**
 * Secret-key message authentication
 * HMAC SHA-512/256
 *
 * @param string $msg
 * @param string $key
 * @return string
 */
function crypto_auth(
    string $msg,
    string $key
): string
{
    return \sodium_crypto_auth($msg, $key);
}

/**
 * Secret-key message verification
 * HMAC SHA-512/256
 *
 * @param string $mac
 * @param string $msg
 * @param string $key
 * @return bool
 */
function crypto_auth_verify(
    string $mac,
    string $msg,
    string $key
): bool
{
    return \sodium_crypto_auth_verify($mac, $msg, $key);
}

/**
 * Public-key authenticated encryption (encrypt)
 * X25519 + Xsalsa20 + Poly1305
 *
 * @param string $msg
 * @param string $nonce
 * @param string $keypair
 * @return string
 */
function crypto_box(
    string $msg,
    string $nonce,
    string $keypair
): string
{
    return \sodium_crypto_box($msg, $nonce, $keypair);
}

/**
 * Generate an X25519 keypair for use with the crypto_box API
 *
 * @return string
 */
function crypto_box_keypair(): string
{
    return \sodium_crypto_box_keypair();
}

/**
 * Derive an X25519 keypair for use with the crypto_box API from a seed
 *
 * @param string $seed
 * @return string
 */
function crypto_box_seed_keypair(
    string $seed
): string
{
    return \sodium_crypto_box_seed_keypair($seed);
}

/**
 * Create an X25519 keypair from an X25519 secret key and X25519 public key
 *
 * @param string $secretkey
 * @param string $publickey
 * @return string
 */
function crypto_box_keypair_from_secretkey_and_publickey(
    string $secretkey,
    string $publickey
): string
{
    return \sodium_crypto_box_keypair_from_secretkey_and_publickey($secretkey, $publickey);
}

/**
 * Public-key authenticated encryption (decrypt)
 * X25519 + Xsalsa20 + Poly1305
 *
 * @param string $msg
 * @param string $nonce
 * @param string $keypair
 * @return string
 */
function crypto_box_open(
    string $msg,
    string $nonce,
    string $keypair
): string
{
    return \sodium_crypto_box_open($msg, $nonce, $keypair);
}

/**
 * Get an X25519 public key from an X25519 keypair
 *
 * @param string $keypair
 * @return string
 */
function crypto_box_publickey(
    string $keypair
): string
{
    return \sodium_crypto_box_publickey($keypair);
}

/**
 * Derive an X25519 public key from an X25519 secret key
 *
 * @param string $secretkey
 * @return string
 */
function crypto_box_publickey_from_secretkey(
    string $secretkey
): string
{
    return \sodium_crypto_box_publickey_from_secretkey($secretkey);
}

/**
 * Anonymous public-key encryption (encrypt)
 * X25519 + Xsalsa20 + Poly1305 + BLAKE2b
 *
 * @param string $message
 * @param string $publickey
 * @return string
 */
function crypto_box_seal(
    string $message,
    string $publickey
): string
{
    return \sodium_crypto_box_seal($message, $publickey);
}

/**
 * Anonymous public-key encryption (decrypt)
 * X25519 + Xsalsa20 + Poly1305 + BLAKE2b
 *
 * @param string $encrypted
 * @param string $keypair
 * @return string
 */
function crypto_box_seal_open(
    string $encrypted,
    string $keypair
): string
{
    return \sodium_crypto_box_seal_open($encrypted, $keypair);
}

/**
 * Extract the X25519 secret key from an X25519 keypair
 *
 * @param string $keypair
 * @return string
 */
function crypto_box_secretkey(string $keypair): string
{
    return \sodium_crypto_box_secretkey($keypair);
}

/**
 * Elliptic Curve Diffie Hellman Key Exchange
 * X25519
 *
 * @param string $secretkey
 * @param string $publickey
 * @param string $client_publickey
 * @param string $server_publickey
 * @return string
 */
function crypto_kx(
    string $secretkey,
    string $publickey,
    string $client_publickey,
    string $server_publickey
): string
{
    return \sodium_crypto_kx($secretkey, $publickey, $client_publickey, $server_publickey);
}

/**
 * Fast and secure cryptographic hash
 *
 * @param string $input
 * @param string $key
 * @param int $length
 * @return string
 */
function crypto_generichash(
    string $input,
    string $key = '',
    int $length = 32
): string
{
    return \sodium_crypto_generichash($input, $key, $length);
}

/**
 * Create a new hash state (e.g. to use for streams)
 * BLAKE2b
 *
 * @param string $key
 * @param int $length
 * @return string
 */
function crypto_generichash_init(
    string $key = '',
    int $length = 32
): string
{
    return \sodium_crypto_generichash_init($key, $length);
}

/**
 * Update the hash state with some data
 * BLAKE2b
 *
 * @param &string $hashState
 * @param string $append
 * @return bool
 */
function crypto_generichash_update(
    string &$hashState,
    string $append
): bool
{
    return \sodium_crypto_generichash_update($hashState, $append);
}

/**
 * Get the final hash
 * BLAKE2b
 *
 * @param string $hashState
 * @param int $length
 * @return string
 */
function crypto_generichash_final(
    string $state,
    int $length = 32
): string
{
    return \sodium_crypto_generichash_final($state, $length);
}

/**
 * Secure password-based key derivation function
 * Argon2i
 *
 * @param int $out_len
 * @param string $passwd
 * @param string $salt
 * @param int $opslimit
 * @param int $memlimit
 * @return $string
 */
function crypto_pwhash(
    int $out_len,
    string $passwd,
    string $salt,
    int $opslimit,
    int $memlimit
): string
{
    return \sodium_crypto_pwhash($out_len, $passwd, $salt, $opslimit, $memlimit);
}

/**
 * Get a formatted password hash (for storage)
 * Argon2i
 *
 * @param string $passwd
 * @param int $opslimit
 * @param int $memlimit
 * @return $string
 */
function crypto_pwhash_str(
    string $passwd,
    int $opslimit,
    int $memlimit
): string
{
    return \sodium_crypto_pwhash_str($passwd, $opslimit, $memlimit);
}

/**
 * Verify a password against a hash
 * Argon2i
 *
 * @param string $hash
 * @param string $passwd
 * @return bool
 */
function crypto_pwhash_str_verify(
    string $hash,
    string $passwd
): bool
{
    return \sodium_crypto_pwhash_str_verify($hash, $passwd);
}

/**
 * Secure password-based key derivation function
 * Scrypt
 *
 * @param int $out_len
 * @param string $passwd
 * @param string $salt
 * @param int $opslimit
 * @param int $memlimit
 * @return $string
 */
function crypto_pwhash_scryptsalsa208sha256(
    int $out_len,
    string $passwd,
    string $salt,
    int $opslimit,
    int $memlimit
): string
{
    return \sodium_crypto_pwhash_scryptsalsa208sha256($out_len, $passwd, $salt, $opslimit, $memlimit);
}

/**
 * Get a formatted password hash (for storage)
 * Scrypt
 *
 * @param string $passwd
 * @param int $opslimit
 * @param int $memlimit
 * @return $string
 */
function crypto_pwhash_scryptsalsa208sha256_str(
    string $passwd,
    int $opslimit,
    int $memlimit
): string
{
    return \sodium_crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit);
}

/**
 * Verify a password against a hash
 * Scrypt
 *
 * @param string $hash
 * @param string $passwd
 * @return bool
 */
function crypto_pwhash_scryptsalsa208sha256_str_verify(
    string $hash,
    string $passwd
): bool
{
    return \sodium_crypto_pwhash_scryptsalsa208sha256_str_verify($hash, $passwd);
}

/**
 * Elliptic Curve Diffie Hellman over Curve25519
 * X25519
 *
 * @param string $ecdhA
 * @param string $ecdhB
 * @return string
 */
function crypto_scalarmult(
    string $ecdhA,
    string $ecdhB
): string
{
    return \sodium_crypto_scalarmult($ecdhA, $ecdhB);
}

/**
 * Authenticated secret-key encryption (encrypt)
 * Xsals20 + Poly1305
 *
 * @param string $plaintext
 * @param string $nonce
 * @param string $key
 * @return string
 */
function crypto_secretbox(
    string $plaintext,
    string $nonce,
    string $key
): string
{
    return \sodium_crypto_secretbox($plaintext, $nonce, $key);
}

/**
 * Authenticated secret-key encryption (decrypt)
 * Xsals20 + Poly1305
 *
 * @param string $ciphertext
 * @param string $nonce
 * @param string $key
 * @return string
 */
function crypto_secretbox_open(
    string $ciphertext,
    string $nonce,
    string $key
): string
{
    return \sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
}

/**
 * A short keyed hash suitable for data structures
 * SipHash-2-4
 *
 * @param string $message
 * @param string $key
 * @return string
 */
function crypto_shorthash(
    string $message,
    string $key
): string
{
    return \sodium_crypto_shorthash($message, $key);
}

/**
 * Digital Signature
 * Ed25519
 *
 * @param string $message
 * @param string $secretkey
 * @return string
 */
function crypto_sign(
    string $message,
    string $secretkey
): string
{
    return \sodium_crypto_sign($message, $secretkey);
}

/**
 * Digital Signature (detached)
 * Ed25519
 *
 * @param string $message
 * @param string $secretkey
 * @return string
 */
function crypto_sign_detached(
    string $message,
    string $secretkey
): string
{
    return \sodium_crypto_sign_detached($message, $secretkey);
}

/**
 * Convert an Ed25519 public key to an X25519 public key
 *
 * @param string $sign_pk
 * @return string
 */
function crypto_sign_ed25519_pk_to_curve25519(
    string $sign_pk
): string
{
    return \sodium_crypto_sign_ed25519_pk_to_curve25519($sign_pk);
}

/**
 * Convert an Ed25519 secret key to an X25519 secret key
 *
 * @param string $sign_sk
 * @return string
 */
function crypto_sign_ed25519_sk_to_curve25519(
    string $sign_sk
): string
{
    return \sodium_crypto_sign_ed25519_sk_to_curve25519($sign_sk);
}

/**
 * Generate an Ed25519 keypair for use with the crypto_sign API
 *
 * @return string
 */
function crypto_sign_keypair(): string
{
    return \sodium_crypto_sign_keypair();
}

/**
 * Create an Ed25519 keypair from an Ed25519 secret key + Ed25519 public key
 *
 * @param string $secretkey
 * @param string $publickey
 * @return string
 */
function crypto_sign_keypair_from_secretkey_and_publickey(
    string $secretkey,
    string $publickey
): string
{
    return \sodium_crypto_sign_keypair_from_secretkey_and_publickey($secretkey, $publickey);
}

/**
 * Verify a signed message and return the plaintext
 *
 * @param string $signed_message
 * @param string $publickey
 * @return string
 */
function crypto_sign_open(
    string $signed_message,
    string $publickey
): string
{
    return \sodium_crypto_sign_open($signed_message, $publickey);
}

/**
 * Get the public key from an Ed25519 keypair
 *
 * @param string $keypair
 * @return string
 */
function crypto_sign_publickey(
    string $keypair
): string
{
    return \sodium_crypto_sign_publickey($keypair);
}

/**
 * Get the secret key from an Ed25519 keypair
 *
 * @param string $keypair
 * @return string
 */
function crypto_sign_secretkey(
    string $keypair
): string
{
    return \sodium_crypto_sign_secretkey($keypair);
}

/**
 * Derive an Ed25519 public key from an Ed25519 secret key
 *
 * @param string $secretkey
 * @return string
 */
function crypto_sign_publickey_from_secretkey(
    string $secretkey
): string
{
    return \sodium_crypto_sign_publickey_from_secretkey($secretkey);
}

/**
 * Derive an Ed25519 keypair for use with the crypto_sign API from a seed
 *
 * @param string $seed
 * @return string
 */
function crypto_sign_seed_keypair(
    string $seed
): string
{
    return \sodium_crypto_sign_seed_keypair($seed);
}

/**
 * Verify a detached signature
 *
 * @param string $signature
 * @param string $msg
 * @param string $publickey
 * @return bool
 */
function crypto_sign_verify_detached(
    string $signature,
    string $msg,
    string $publickey
): bool
{
    return \sodium_crypto_sign_verify_detached($signature, $msg, $publickey);
}

/**
 * Create a keystream from a key and nonce
 * Xsalsa20
 *
 * @param int $length
 * @param string $nonce
 * @param string $key
 * @return string
 */
function crypto_stream(
    int $length,
    string $nonce,
    string $key
): string
{
    return \sodium_crypto_stream($length, $nonce, $key);
}

/**
 * Encrypt a message using a stream cipher
 * Xsalsa20
 *
 * @param string $plaintext
 * @param string $nonce
 * @param string $key
 * @return string
 */
function crypto_stream_xor(
    string $plaintext,
    string $nonce,
    string $key
): string
{
    return \sodium_crypto_stream_xor($plaintext, $nonce, $key);
}

/**
 * Generate a string of random bytes
 * /dev/urandom
 *
 * @param int $length
 * @return string
 */
function randombytes_buf(
    int $length
): string
{
    return \random_bytes($length);
}

/**
 * Generate a 16-bit integer
 * /dev/urandom
 *
 * @return int
 */
function randombytes_random16(): string
{
    return \random_int(0, 65535);
}

/**
 * Generate an unbiased random integer between 0 and a specified value
 * /dev/urandom
 *
 * @param int $upperBoundNonInclusive
 * @return int
 */
function randombytes_uniform(
    int $upperBoundNonInclusive
): int
{
    return \sodium_randombytes_uniform($upperBoundNonInclusive);
}

/**
 * Convert to hex without side-chanels
 *
 * @param string $binary
 * @return string
 */
function bin2hex(
    string $binary
): string
{
    return \sodium_bin2hex($binary);
}

/**
 * Compare two strings in constant time
 *
 * @param string $left
 * @param string $right
 * @return int
 */
function compare(
    string $left,
    string $right
): int
{
    return \sodium_compare($left, $right);
}

/**
 * Convert from hex without side-chanels
 *
 * @param string $binary
 * @return string
 */
function hex2bin(
    string $hex
): string
{
    return \sodium_hex2bin($hex);
}

/**
 * Increment a string in little-endian
 *
 * @param &string $nonce
 * @return string
 */
function increment(
    string &$nonce
)
{
    \sodium_increment($nonce);
}

/**
 * Add the right operand to the left
 *
 * @param &string $left
 * @param string $right
 */
function add(
    string &$left,
    string $right
)
{
    \sodium_add($left, $right);
}

/**
 * Get the true major version of libsodium
 * @return int
 */
function library_version_major(): int
{
    return \sodium_library_version_major();
}

/**
 * Get the true minor version of libsodium
 * @return int
 */
function library_version_minor(): int
{
    return \sodium_library_version_minor();
}

/**
 * Compare two strings in constant time
 *
 * @param string $left
 * @param string $right
 * @return int
 */
function memcmp(
    string $left,
    string $right
): int
{
    return \sodium_memcmp($right, $left);
}

/**
 * Wipe a buffer
 *
 * @param &string $nonce
 */
function memzero(
    string &$target
)
{
    \sodium_memzero($target);
}

/**
 * Get the version string
 *
 * @return string
 */
function version_string(): string
{
    return \sodium_version_string();
}

/**
 * Scalar multiplication of the base point and your key
 *
 * @param string $sk
 * @return string
 */
function crypto_scalarmult_base(
    string $sk
): string
{
    return \sodium_crypto_scalarmult_base($sk);
}