<?php
declare(strict_types=1);

return [
    'providers' => [
        'openai' => [
            'name'     => 'OpenAI',
            'models'   => ['gpt-4o-mini', 'gpt-4o', 'gpt-4-turbo'],
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
        ],
        'gemini' => [
            'name'     => 'Google Gemini',
            'models'   => ['gemini-1.5-flash', 'gemini-1.5-pro'],
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
        ],
        'anthropic' => [
            'name'     => 'Anthropic Claude',
            'models'   => ['claude-3-5-sonnet-20241022', 'claude-3-haiku-20240307'],
            'endpoint' => 'https://api.anthropic.com/v1/messages',
        ],
        'groq' => [
            'name'     => 'Groq (Ultra-Fast)',
            'models'   => ['llama-3.3-70b-versatile', 'mixtral-8x7b-32768'],
            'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
        ],
    ],
];
