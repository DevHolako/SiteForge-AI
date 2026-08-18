<?php

declare(strict_types=1);

return [
    [
        'id'       => 'restaurant',
        'title'    => __('Italian Restaurant & Pizzeria', 'siteforge-ai'),
        'category' => __('Food & Dining', 'siteforge-ai'),
        'prompt'   => 'An elegant Italian pizzeria and wine bar in downtown Paris with online table reservation, chef specials menu, and photo gallery.',
        'theme'    => 'astra',
        'plugins'  => ['wpforms-lite', 'restaurant-reservations'],
    ],
    [
        'id'       => 'ecommerce',
        'title'    => __('Modern Fashion Boutique', 'siteforge-ai'),
        'category' => __('E-Commerce', 'siteforge-ai'),
        'prompt'   => 'A minimalist modern fashion brand with product collections, size guide, filter by color, customer reviews, and cart drawer.',
        'theme'    => 'oceanwp',
        'plugins'  => ['woocommerce', 'woo-smart-wishlist'],
    ],
    [
        'id'       => 'saas',
        'title'    => __('AI Tech Startup & SaaS', 'siteforge-ai'),
        'category' => __('Technology', 'siteforge-ai'),
        'prompt'   => 'A B2B AI analytics platform with pricing tiers, feature comparison grid, testimonial slider, and contact demo form.',
        'theme'    => 'kadence',
        'plugins'  => ['wpforms-lite', 'kadence-blocks'],
    ],
    [
        'id'       => 'clinic',
        'title'    => __('Dental & Medical Clinic', 'siteforge-ai'),
        'category' => __('Healthcare', 'siteforge-ai'),
        'prompt'   => 'A modern dental practice with online appointment booking, doctor profiles, service cards, and patient reviews.',
        'theme'    => 'neve',
        'plugins'  => ['wpforms-lite', 'easy-appointments'],
    ],
    [
        'id'       => 'realestate',
        'title'    => __('Luxury Real Estate Agency', 'siteforge-ai'),
        'category' => __('Real Estate', 'siteforge-ai'),
        'prompt'   => 'Luxury property listings with interactive neighborhood map, agent profiles, mortgage calculator, and inquiry form.',
        'theme'    => 'astra',
        'plugins'  => ['wpforms-lite', 'essential-blocks'],
    ],
];
