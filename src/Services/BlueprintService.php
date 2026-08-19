<?php

declare(strict_types=1);

namespace SiteForgeAI\Services;

use SiteForgeAI\Services\AI\AIClientInterface;
use SiteForgeAI\Services\AI\AIFactory;

class BlueprintService
{
    private AIClientInterface $ai;

    public function __construct(?AIClientInterface $ai = null)
    {
        $this->ai = $ai ?: AIFactory::create();
    }

    /**
     * Enhance a brief user prompt into an architectural design brief.
     *
     * @param string $userPrompt
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function enhancePrompt(string $userPrompt, array $options = []): array
    {
        $systemPrompt = <<<PROMPT
You are a World-Class WordPress Architect and Digital Strategist.
Your goal is to transform a brief user concept into an inspiring, comprehensive, and production-ready WordPress site specification.

You must respond ONLY with a valid JSON object matching this schema:
{
  "enhanced_prompt": "A vivid, comprehensive description of the site with key features and value propositions",
  "target_audience": "Detailed description of primary visitors and customers",
  "brand_vibe": "3-5 descriptive tone keywords (e.g. Luxurious, Modern, Trustworthy)",
  "suggested_pages": ["Home", "About", "Services", "Portfolio", "Contact"],
  "color_palette": {
    "primary": "#HexCode",
    "secondary": "#HexCode",
    "accent": "#HexCode",
    "background": "#HexCode"
  },
  "recommended_theme": "astra" // or "neve", "generatepress", "oceanwp"
}
PROMPT;

        $userMessage = sprintf("Brief User Concept: \"%s\"\n\nGenerate the enhanced architectural design brief in JSON format.", $userPrompt);

        $result = $this->ai->generateJson($userMessage, [], [
            'system_prompt' => $systemPrompt,
            'temperature'   => 0.7,
        ]);

        return array_merge([
            'original_prompt' => $userPrompt,
        ], $result);
    }

    /**
     * Get tailored Theme and Plugin suggestions for Manual Mode.
     *
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function getSuggestions(string $prompt, array $options = []): array
    {
        $systemPrompt = <<<PROMPT
You are SiteForge AI. Analyze the user's website concept and recommend the best themes and plugins from wordpress.org repository.

You must respond ONLY with a valid JSON object:
{
  "niche": "restaurant",
  "recommended_theme": "astra",
  "themes": [
    { "slug": "astra", "name": "Astra", "description": "Lightweight, customizable, and fast multipurpose theme.", "recommended": true },
    { "slug": "neve", "name": "Neve", "description": "Modern block-based and responsive theme.", "recommended": false },
    { "slug": "oceanwp", "name": "OceanWP", "description": "Feature-rich theme with deep e-commerce support.", "recommended": false }
  ],
  "plugins": [
    { "slug": "elementor", "name": "Elementor", "description": "Leading drag-and-drop page builder.", "recommended": true },
    { "slug": "wpforms-lite", "name": "WPForms Lite", "description": "Easy and intuitive drag-and-drop contact forms.", "recommended": true },
    { "slug": "woocommerce", "name": "WooCommerce", "description": "Full e-commerce store functionality.", "recommended": false },
    { "slug": "yoast-seo", "name": "Yoast SEO", "description": "Search engine optimization and XML sitemaps.", "recommended": true }
  ]
}
PROMPT;

        $userMessage = sprintf("Website Concept: \"%s\"\n\nGenerate recommended themes and plugins for this project.", $prompt);

        return $this->ai->generateJson($userMessage, [], [
            'system_prompt' => $systemPrompt,
            'temperature'   => 0.5,
        ]);
    }

    /**
     * Generate complete WordPress Site Blueprint (Theme, Plugins, Pages, Customizer, Starter Content).
     *
     * @param string $prompt
     * @param array<string, mixed> $options (e.g. niche, preferred_theme, extra_plugins)
     * @return array<string, mixed>
     */
    public function generateBlueprint(string $prompt, array $options = []): array
    {
        $systemPrompt = <<<PROMPT
You are SiteForge AI, the master WordPress site generator.
Generate a complete, production-ready WordPress site architecture blueprint in pure JSON.
The blueprint will be used by automated WordPress seeders and package installers.

Available popular themes from wordpress.org repository: "astra", "neve", "generatepress", "oceanwp", "twentytwentyfour".
Standard recommended plugins: "elementor", "contact-form-7", "woocommerce", "yoast-seo", "wpforms-lite", "spectra".

Output MUST be a valid JSON object with the following strict structure:
{
  "site": {
    "title": "Site Title",
    "tagline": "A catchy tagline",
    "niche": "restaurant" // or business, ecommerce, portfolio, blog, etc.
  },
  "theme": {
    "slug": "astra",
    "name": "Astra",
    "source": "wporg"
  },
  "plugins": [
    { "slug": "elementor", "name": "Elementor", "source": "wporg", "required": true, "description": "Page builder" },
    { "slug": "wpforms-lite", "name": "WPForms Lite", "source": "wporg", "required": false, "description": "Contact form" }
  ],
  "pages": [
    {
      "title": "Home",
      "slug": "home",
      "is_front_page": true,
      "sections": [
        { "type": "hero", "heading": "Welcome to...", "content": "Compelling hero description...", "cta_text": "Get Started", "cta_url": "/contact" },
        { "type": "features", "heading": "Our Services", "content": "Feature descriptions..." },
        { "type": "cta", "heading": "Ready to book?", "content": "Call us today." }
      ]
    },
    {
      "title": "About Us",
      "slug": "about-us",
      "is_front_page": false,
      "sections": [
        { "type": "story", "heading": "Our Story", "content": "Detailed company story..." }
      ]
    },
    {
      "title": "Contact",
      "slug": "contact",
      "is_front_page": false,
      "sections": [
        { "type": "contact_info", "heading": "Get In Touch", "content": "Email, phone, and location details..." }
      ]
    }
  ],
  "posts": [
    {
      "title": "Welcome to our new website",
      "slug": "welcome-to-our-new-website",
      "category": "News",
      "excerpt": "Exciting news as we launch our new online presence.",
      "content": "Full article content introducing the brand..."
    }
  ],
  "customizer": {
    "colors": {
      "primary": "#1E40AF",
      "secondary": "#3B82F6",
      "accent": "#F59E0B",
      "background": "#F8FAFC"
    },
    "typography": {
      "headings_font": "Inter",
      "body_font": "Inter"
    }
  },
  "navigation": [
    { "label": "Home", "url": "/" },
    { "label": "About", "url": "/about-us" },
    { "label": "Contact", "url": "/contact" }
  ]
}
PROMPT;

        $userMessage = sprintf(
            "User Requirements:\nDescription: %s\nNiche: %s\nPreferred Theme: %s\n\nGenerate the complete JSON site blueprint.",
            $prompt,
            $options['niche'] ?? 'general',
            $options['theme'] ?? 'astra'
        );

        $blueprint = $this->ai->generateJson($userMessage, [], [
            'system_prompt' => $systemPrompt,
            'temperature'   => (float) ($options['temperature'] ?? 0.7),
            'max_tokens'    => 4000,
        ]);

        return $this->sanitizeBlueprint($blueprint, $prompt);
    }

    /**
     * Verify and fill defaults for any missing properties in the generated blueprint.
     *
     * @param array<string, mixed> $blueprint
     * @param string $fallbackTitle
     * @return array<string, mixed>
     */
    private function sanitizeBlueprint(array $blueprint, string $fallbackTitle): array
    {
        $site = is_array($blueprint['site'] ?? null) ? $blueprint['site'] : [];
        $site['title'] = !empty($site['title']) ? (string) $site['title'] : 'My New Website';
        $site['tagline'] = !empty($site['tagline']) ? (string) $site['tagline'] : 'Built with SiteForge AI';
        $site['niche'] = !empty($site['niche']) ? (string) $site['niche'] : 'business';

        $theme = is_array($blueprint['theme'] ?? null) ? $blueprint['theme'] : [];
        $theme['slug'] = !empty($theme['slug']) ? (string) $theme['slug'] : 'astra';
        $theme['name'] = !empty($theme['name']) ? (string) $theme['name'] : ucfirst($theme['slug']);
        $theme['source'] = 'wporg';

        $plugins = is_array($blueprint['plugins'] ?? null) ? $blueprint['plugins'] : [];
        $pages = is_array($blueprint['pages'] ?? null) ? $blueprint['pages'] : [];
        $posts = is_array($blueprint['posts'] ?? null) ? $blueprint['posts'] : [];
        $customizer = is_array($blueprint['customizer'] ?? null) ? $blueprint['customizer'] : [];
        $navigation = is_array($blueprint['navigation'] ?? null) ? $blueprint['navigation'] : [];

        // Ensure at least Home and Contact pages exist
        if (empty($pages)) {
            $pages = [
                [
                    'title'         => 'Home',
                    'slug'          => 'home',
                    'is_front_page' => true,
                    'sections'      => [
                        [
                            'type'     => 'hero',
                            'heading'  => $site['title'],
                            'content'  => $site['tagline'],
                            'cta_text' => 'Learn More',
                            'cta_url'  => '/contact',
                        ],
                    ],
                ],
                [
                    'title'         => 'Contact',
                    'slug'          => 'contact',
                    'is_front_page' => false,
                    'sections'      => [
                        [
                            'type'    => 'contact_info',
                            'heading' => 'Contact Us',
                            'content' => 'Get in touch with us today.',
                        ],
                    ],
                ],
            ];
        }

        return [
            'site'       => $site,
            'theme'      => $theme,
            'plugins'    => $plugins,
            'pages'      => $pages,
            'posts'      => $posts,
            'customizer' => $customizer,
            'navigation' => $navigation,
        ];
    }
}
