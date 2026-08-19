<?php

declare(strict_types=1);

use SiteForgeAI\Services\Seeder\BlockBuilder;

test('it renders hero block with heading, content, and cta button', function () {
    $builder = new BlockBuilder();
    $html = $builder->renderHero([
        'heading'  => 'Grand Hotel Palace',
        'content'  => 'Luxury in the city center',
        'cta_text' => 'Book Room',
        'cta_url'  => '/contact',
    ]);

    expect($html)->toContain('wp:group')
        ->toContain('Grand Hotel Palace')
        ->toContain('Luxury in the city center')
        ->toContain('Book Room')
        ->toContain('/contact');
});

test('it renders features section with three columns', function () {
    $builder = new BlockBuilder();
    $html = $builder->renderFeatures([
        'heading' => 'Our Core Services',
        'content' => 'Everything you need',
    ]);

    expect($html)->toContain('wp:columns')
        ->toContain('Our Core Services')
        ->toContain('Quality & Craft')
        ->toContain('Seamless Experience');
});

test('it renders contact info block with address and phone', function () {
    $builder = new BlockBuilder();
    $html = $builder->renderContactInfo([
        'heading' => 'Contact Us',
        'content' => 'Send us a message',
    ]);

    expect($html)->toContain('Contact Us')
        ->toContain('📍 Address')
        ->toContain('📞 Phone & Email');
});
