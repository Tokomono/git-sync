<?php

namespace Tokomono\GitSync\Contracts;

interface GitProviderInterface
{
    /**
     * Create a new branch from a source branch.
     */
    public function createBranch(string $sourceBranch, string $newBranch): void;

    /**
     * Commit a file to the specified branch.
     */
    public function commitFile(string $branch, string $filePath, string $content, string $commitMessage): void;

    /**
     * Create a pull request.
     *
     * @return string The URL of the created Pull Request.
     */
    public function createPullRequest(string $sourceBranch, string $targetBranch, string $title, string $description): string;
}
