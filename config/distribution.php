<?php

return [

    'oauth' => [
        'youtube' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI', '/distribution/accounts/callback/youtube'),
            'scopes' => [
                'https://www.googleapis.com/auth/youtube.upload',
                'https://www.googleapis.com/auth/youtube.readonly',
            ],
        ],
        'spotify' => [
            'client_id' => env('SPOTIFY_CLIENT_ID'),
            'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
            'redirect' => env('SPOTIFY_REDIRECT_URI', '/distribution/accounts/callback/spotify'),
            'scopes' => ['user-read-email', 'playlist-modify-public'],
        ],
        'facebook' => [
            'client_id' => env('META_APP_ID'),
            'client_secret' => env('META_APP_SECRET'),
            'redirect' => env('META_REDIRECT_URI', '/distribution/accounts/callback/facebook'),
            'scopes' => ['pages_manage_posts', 'pages_read_engagement'],
        ],
        'instagram' => [
            'client_id' => env('META_APP_ID'),
            'client_secret' => env('META_APP_SECRET'),
            'redirect' => env('META_REDIRECT_URI', '/distribution/accounts/callback/instagram'),
            'scopes' => ['instagram_basic', 'instagram_content_publish'],
        ],
        'linkedin' => [
            'client_id' => env('LINKEDIN_CLIENT_ID'),
            'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
            'redirect' => env('LINKEDIN_REDIRECT_URI', '/distribution/accounts/callback/linkedin'),
            'scopes' => ['w_member_social', 'openid', 'profile', 'email'],
        ],
    ],

    'default_costs' => [
        'youtube' => 50,
        'spotify' => 30,
        'audiomack' => 20,
        'instagram' => 25,
        'linkedin' => 25,
        'facebook' => 25,
        'website' => 10,
    ],

    'requires_oauth' => ['youtube', 'spotify', 'facebook', 'instagram', 'linkedin'],

];
