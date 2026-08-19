<?php

declare(strict_types=1);

use SiteForgeAI\Core\Route;
use SiteForgeAI\Controllers\RollbackController;

Route::get('rollback/list', RollbackController::class, 'list');
Route::get('rollback/batches', RollbackController::class, 'list');
Route::post('rollback/revert', RollbackController::class, 'revert');
Route::post('rollback/execute', RollbackController::class, 'revert');
