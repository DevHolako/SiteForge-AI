<?php

declare(strict_types=1);


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
