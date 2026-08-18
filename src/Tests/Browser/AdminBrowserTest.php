<?php

declare(strict_types=1);

// Only launch visible browser window during local testing, NOT in CI/CD mode
$isCi = !empty($_SERVER['CI'])
    || getenv('CI') !== false
    || in_array('--ci', $_SERVER['argv'] ?? [], true);

if (!$isCi) {
    pest()->browser()->headed();
}

test('it visits WordPress homepage in real browser and verifies no errors', function () {
    $page = visit('http://localhost/wordpress/');

    $page->assertNoJavaScriptErrors()
         ->assertNoConsoleLogs();
});

test('it visits WordPress wp-login page in real browser and checks login form', function () {
    $page = visit('http://localhost/wordpress/wp-login.php');

    $page->assertNoJavaScriptErrors()
         ->assertSee('Log In');
});
