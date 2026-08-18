<?php
declare(strict_types=1);

use SiteForgeAI\Core\Route;
use SiteForgeAI\Controllers\RollbackController;

Route::get('rollback/batches', RollbackController::class, 'listBatches');
Route::post('rollback/execute', RollbackController::class, 'rollback');
