<?php

declare(strict_types=1);

use SiteForgeAI\Services\Seeder\SiteSeederService;
use SiteForgeAI\Services\Seeder\BlockBuilder;

test('it seeds pages with AI metadata and sets static front page', function () {
    $seeder = new SiteSeederService(new BlockBuilder());

    $pages = [
        [
            'title'         => 'Test Home Page',
            'slug'          => 'test-home',
            'is_front_page' => true,
            'sections'      => [
                ['type' => 'hero', 'heading' => 'Home Hero', 'content' => 'Welcome home'],
            ],
        ],
    ];

    $created = $seeder->seedPages($pages, 'batch_test_1');

    expect($created)->toBeArray()->toHaveKey('test-home');

    $homeId = $created['test-home'];
    expect((int) get_option('page_on_front'))->toBe($homeId)
        ->and(get_post_meta($homeId, SiteSeederService::AI_GENERATED_META, true))->toBe('1')
        ->and(get_post_meta($homeId, SiteSeederService::BATCH_ID_META, true))->toBe('batch_test_1');
});

test('it removes ONLY AI generated mock data and preserves real user posts', function () {
    $seeder = new SiteSeederService(new BlockBuilder());

    // 1. Create a real human user post (no AI meta)
    $realUserPostId = wp_insert_post([
        'post_title'   => 'Real User Authentic Article',
        'post_content' => 'Content written by human.',
        'post_status'  => 'publish',
        'post_type'    => 'post',
    ]);

    // 2. Seed AI mock posts
    $aiPostIds = $seeder->seedPosts([
        ['title' => 'AI Mock Article 1', 'content' => 'Generated content'],
        ['title' => 'AI Mock Article 2', 'content' => 'Generated content'],
    ], 'batch_mock_test');

    expect($aiPostIds)->toHaveCount(2);

    // 3. Trigger removeMockData()
    $summary = $seeder->removeMockData('batch_mock_test');

    expect($summary['posts_deleted'])->toBe(2);

    // 4. Verify AI posts are gone
    expect(get_post($aiPostIds[0]))->toBeNull()
        ->and(get_post($aiPostIds[1]))->toBeNull();

    // 5. Verify Real User Post is STILL ALIVE and UNTOUCHED!
    $realPost = get_post($realUserPostId);
    expect($realPost)->not->toBeNull()
        ->and($realPost->post_title)->toBe('Real User Authentic Article');

    // Cleanup real test post
    wp_delete_post($realUserPostId, true);
});
