<?php

namespace Tokomono\GitSync\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Tokomono\GitSync\Contracts\GitProviderInterface;

/**
 * Bitbucket API Service Provider for Git-Sync
 *
 * HOW TO CONFIGURE BITBUCKET:
 * 1. Go to Bitbucket -> Personal Settings -> App Passwords
 * 2. Click "Create app password"
 * 3. Give it a descriptive name (e.g. "GitSync App")
 * 4. Permissions required:
 *    - Repositories: Read, Write
 *    - Pull Requests: Read, Write
 * 5. Copy the password and add it to your .env file:
 *    BITBUCKET_USERNAME=your_bitbucket_username
 *    BITBUCKET_PASSWORD=your_app_password
 *    BITBUCKET_WORKSPACE=your_workspace_slug
 *    BITBUCKET_REPOSITORY=your_repo_slug
 *
 * HOW TO TEST LOCALLY:
 * We use the Bitbucket REST API v2.
 * Endpoint: https://api.bitbucket.org/2.0
 */
class BitbucketProvider implements GitProviderInterface
{
    protected array $config;

    protected string $baseUrl = 'https://api.bitbucket.org/2.0';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function client()
    {
        return Http::withBasicAuth($this->config['username'], $this->config['password'])
            ->baseUrl($this->baseUrl.'/repositories/'.$this->config['workspace'].'/'.$this->config['repository']);
    }

    public function createBranch(string $sourceBranch, string $newBranch): void
    {
        // 1. Get the hash of the source branch
        $response = $this->client()->get("/refs/branches/{$sourceBranch}");

        if (! $response->successful()) {
            throw new Exception("Could not fetch source branch {$sourceBranch} from Bitbucket: ".$response->body());
        }

        $hash = $response->json('target.hash');

        // 2. Create the new branch
        $createResponse = $this->client()->post('/refs/branches', [
            'name' => $newBranch,
            'target' => [
                'hash' => $hash,
            ],
        ]);

        if (! $createResponse->successful()) {
            throw new Exception("Could not create new branch {$newBranch} on Bitbucket: ".$createResponse->body());
        }
    }

    public function commitFile(string $branch, string $filePath, string $content, string $commitMessage): void
    {
        // Bitbucket API expects a multipart/form-data request for creating/updating a commit.
        $commitResponse = $this->client()->asMultipart()->post('/src', [
            [
                'name' => 'message',
                'contents' => $commitMessage,
            ],
            [
                'name' => 'branch',
                'contents' => $branch,
            ],
            [
                'name' => $filePath,
                'contents' => $content,
            ],
        ]);

        if (! $commitResponse->successful()) {
            throw new Exception("Could not commit file {$filePath} to Bitbucket: ".$commitResponse->body());
        }
    }

    public function createPullRequest(string $sourceBranch, string $targetBranch, string $title, string $description): string
    {
        $response = $this->client()->post('/pullrequests', [
            'title' => $title,
            'description' => $description,
            'source' => [
                'branch' => [
                    'name' => $sourceBranch,
                ],
            ],
            'destination' => [
                'branch' => [
                    'name' => $targetBranch,
                ],
            ],
        ]);

        if (! $response->successful()) {
            throw new Exception('Could not create Pull Request on Bitbucket: '.$response->body());
        }

        $links = $response->json('links');

        return $links['html']['href'] ?? '';
    }
}
