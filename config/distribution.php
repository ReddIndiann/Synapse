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

    /*
    |--------------------------------------------------------------------------
    | External platform URLs (login, signup, home, developer consoles)
    |--------------------------------------------------------------------------
    */
    'platform_urls' => [
        'youtube' => [
            'login' => 'https://accounts.google.com/signin',
            'signup' => 'https://accounts.google.com/signup',
            'home' => 'https://studio.youtube.com',
            'developer' => 'https://console.cloud.google.com/apis/credentials',
        ],
        'spotify' => [
            'login' => 'https://accounts.spotify.com/login',
            'signup' => 'https://www.spotify.com/signup',
            'home' => 'https://open.spotify.com',
            'developer' => 'https://developer.spotify.com/dashboard',
        ],
        'facebook' => [
            'login' => 'https://www.facebook.com/login',
            'signup' => 'https://www.facebook.com/r.php',
            'home' => 'https://www.facebook.com',
            'developer' => 'https://developers.facebook.com/apps',
        ],
        'instagram' => [
            'login' => 'https://www.instagram.com/accounts/login',
            'signup' => 'https://www.instagram.com/accounts/emailsignup',
            'home' => 'https://www.instagram.com',
            'developer' => 'https://developers.facebook.com/apps',
        ],
        'linkedin' => [
            'login' => 'https://www.linkedin.com/login',
            'signup' => 'https://www.linkedin.com/signup',
            'home' => 'https://www.linkedin.com/feed',
            'developer' => 'https://www.linkedin.com/developers/apps',
        ],
        'audiomack' => [
            'login' => 'https://audiomack.com/login',
            'signup' => 'https://audiomack.com/join',
            'home' => 'https://audiomack.com',
        ],
        'website' => [
            'home' => 'https://synapse.local',
        ],
    ],

];
