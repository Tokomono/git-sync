<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Git Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default git provider that will be used by the
    | library to create branches and pull requests.
    | Supported: "github", "bitbucket"
    |
    */
    'default_provider' => env('GIT_SYNC_PROVIDER', 'github'),

    /*
    |--------------------------------------------------------------------------
    | Target Branches
    |--------------------------------------------------------------------------
    |
    | The branches that are allowed to be selected as a target for the PR.
    |
    */
    'target_branches' => ['main', 'master', 'develop', 'staging'],

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'github' => [
            'token' => env('GITHUB_TOKEN'),
            // Format: 'owner/repo'
            'repository' => env('GITHUB_REPOSITORY'),
        ],
        'bitbucket' => [
            'username' => env('BITBUCKET_USERNAME'),
            // App Password or Workspace Token
            'password' => env('BITBUCKET_PASSWORD'),
            'workspace' => env('BITBUCKET_WORKSPACE'),
            'repository' => env('BITBUCKET_REPOSITORY'),
        ],
    ],
];
