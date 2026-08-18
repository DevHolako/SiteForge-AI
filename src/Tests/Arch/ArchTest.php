<?php

arch('it enforces strict types across all plugin code')
    ->expect('SiteForgeAI')
    ->toUseStrictTypes();

arch('it ensures no debugging statements are left in production code')
    ->expect('SiteForgeAI')
    ->not->toUse(['var_dump', 'dd', 'dump', 'print_r']);

arch('it ensures all controllers extend BaseController')
    ->expect('SiteForgeAI\Controllers')
    ->toExtend('SiteForgeAI\Controllers\BaseController')
    ->ignoring('SiteForgeAI\Controllers\BaseController');