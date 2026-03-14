<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI / LLM Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI-compatible API endpoints.
    | Currently using Cliprox API as the LLM provider.
    |
    */

    'base_url' => env('AI_BASE_URL', 'https://cliprox.digitalinid.cloud'),
    'api_key' => env('AI_API_KEY', ''),
    'model' => env('AI_MODEL', 'gpt-5.4'),

    /*
    |--------------------------------------------------------------------------
    | Available Models
    |--------------------------------------------------------------------------
    |
    | List of available models from the AI provider.
    |
    */
    'models' => [
        'gpt-5.4' => 'GPT-5.4 (Latest)',
        'gpt-5.3-codex-spark' => 'GPT-5.3 Codex Spark',
        'gpt-5.3-codex' => 'GPT-5.3 Codex',
        'gpt-5.2-codex' => 'GPT-5.2 Codex',
        'gpt-5.2' => 'GPT-5.2',
        'gpt-5.1-codex-max' => 'GPT-5.1 Codex Max',
        'gpt-5.1-codex-mini' => 'GPT-5.1 Codex Mini',
        'gpt-5.1-codex' => 'GPT-5.1 Codex',
        'gpt-5.1' => 'GPT-5.1',
        'gpt-5-codex-mini' => 'GPT-5 Codex Mini',
        'gpt-5-codex' => 'GPT-5 Codex',
        'gpt-5' => 'GPT-5',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Request Options
    |--------------------------------------------------------------------------
    |
    | Default parameters for AI API requests.
    |
    */
    'defaults' => [
        'temperature' => 0.7,
        'max_tokens' => 2000,
        'timeout' => 120,
    ],
];
