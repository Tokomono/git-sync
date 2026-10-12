<?php

namespace Tokomono\GitSync\Services;

use Illuminate\Support\Facades\Http;
use Tokomono\GitSync\Contracts\GitProviderInterface;
use Exception;

/**
 * GitHub API Service Provider for Git-Sync
 *
 * HOW TO CONFIGURE GITHUB:
 * 1. Go to GitHub.com -> Settings -> Developer Settings -> Personal Access Tokens -> Tokens (classic)
 * 2. Click "Generate new token (classic)"
 * 3. Give it a descriptive name (e.g. "GitSync App")
 * 4. Scopes required:
 *    - `repo` (Full control of private repositories) -> Needed to read/write files and create branches/PRs.
 * 5. Copy the token and add it to your .env file:
 *    GITHUB_TOKEN=ghp_your_token_here
 *    GITHUB_REPOSITORY=your-organization/your-repo-name
 *
 * HOW TO TEST LOCALLY:
 * We use the GitHub REST API v3. 
 * Endpoint: https://api.github.com
 */
class GitHubProvider implements GitProviderInterface
{
    protected array $config;
    protected string $baseUrl = 'https://api.github.com';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function client()
    {
        return Http::withToken($this->config['token'])
            ->withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->baseUrl($this->baseUrl . '/repos/' . $this->config['repository'] . '/');
    }

    public function createBranch(string $sourceBranch, string $newBranch): void
    {
        // 1. Get the SHA of the source branch
        $response = $this->client()->get("git/ref/heads/{$sourceBranch}");
        
        if (!$response->successful()) {
            throw new Exception("Could not fetch source branch {$sourceBranch} from GitHub: " . $response->body());
        }

        $sha = $response->json('object.sha');

        // 2. Create the new branch
        $createResponse = $this->client()->post("git/refs", [
            'ref' => "refs/heads/{$newBranch}",
            'sha' => $sha,
        ]);

        if (!$createResponse->successful()) {
            throw new Exception("Could not create new branch {$newBranch} on GitHub: " . $createResponse->body());
        }
    }

    public function commitFile(string $branch, string $filePath, string $content, string $commitMessage): void
    {
        // GitHub API requires the SHA of the file if it already exists, to overwrite it.
        $fileResponse = $this->client()->get("contents/{$filePath}", [
            'ref' => $branch
        ]);

        $sha = null;
        if ($fileResponse->successful()) {
            $sha = $fileResponse->json('sha');
        }

        $payload = [
            'message' => $commitMessage,
            'content' => base64_encode($content),
            'branch' => $branch,
        ];

        if ($sha) {
            $payload['sha'] = $sha;
        }

        $commitResponse = $this->client()->put("contents/{$filePath}", $payload);

        if (!$commitResponse->successful()) {
            throw new Exception("Could not commit file {$filePath} to GitHub: " . $commitResponse->body());
        }
    }

    public function createPullRequest(string $sourceBranch, string $targetBranch, string $title, string $description): string
    {
        $response = $this->client()->post("pulls", [
            'title' => $title,
            'head' => $sourceBranch,
            'base' => $targetBranch,
            'body' => $description,
        ]);

        if (!$response->successful()) {
            throw new Exception("Could not create Pull Request on GitHub: " . $response->body());
        }

        return $response->json('html_url');
    }
}
