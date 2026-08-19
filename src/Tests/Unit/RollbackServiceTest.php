<?php

declare(strict_types=1);

use SiteForgeAI\Services\RollbackService;

test('it records and retrieves snapshots', function () {
    $service = new RollbackService();

    $snapshot = [
        'batch_id'   => 'batch_test_123',
        'created_at' => time() + 5000,
        'pages'      => [99991, 99992],
        'posts'      => [99993],
    ];

    $service->recordSnapshot($snapshot);

    $all = $service->getSnapshots();
    expect($all)->toHaveKey('batch_test_123');

    $latest = $service->getLatestSnapshot();
    expect($latest['batch_id'])->toBe('batch_test_123');
});
