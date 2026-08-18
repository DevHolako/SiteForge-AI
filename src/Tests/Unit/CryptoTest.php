<?php

declare(strict_types=1);

use SiteForgeAI\Security\Crypto;

beforeEach(function () {
    update_option('siteforge_ai_encryption_key', bin2hex(random_bytes(32)));
});

test('it encrypts and decrypts a plain text string successfully', function () {
    $plain = 'sk-proj-test-123456789-secret-api-key';

    $encrypted = Crypto::encrypt($plain);

    expect($encrypted)
        ->toBeString()
        ->not->toBeEmpty()
        ->not->toBe($plain);

    $decrypted = Crypto::decrypt($encrypted);

    expect($decrypted)->toBe($plain);
});

test('it returns an empty string when encrypting an empty string', function () {
    expect(Crypto::encrypt(''))->toBe('');
});

test('it returns an empty string when decrypting invalid or empty data', function () {
    expect(Crypto::decrypt(''))->toBe('');
    expect(Crypto::decrypt('invalid_base64_payload'))->toBe('');
    expect(Crypto::decrypt(base64_encode('short')))->toBe('');
});
