<?php
declare(strict_types=1);

use SiteForgeAI\Core\Route;
use SiteForgeAI\Controllers\AIController;

Route::post('ai/enhance', AIController::class, 'enhance');
Route::post('ai/blueprint', AIController::class, 'blueprint');
