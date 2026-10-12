<?php

namespace Tokomono\GitSync\Contracts;

interface GitProviderInterface
{
    /**
     * Create a new branch from a source branch.
     *
     * @param string $sourceBranch
     * @param string $newBranch
     * @return void
     */
    public function createBranch(string $sourceBranch, string $newBranch): void;

    /**
     * Commit a file to the specified branch.
     *
     * @param string $branch
     * @param string $filePath
     * @param string $content
     * @param string $commitMessage
     * @return void
     */
    public function commitFile(string $branch, string $filePath, string $content, string $commitMessage): void;

    /**
     * Create a pull request.
     *
     * @param string $sourceBranch
     * @param string $targetBranch
     * @param string $title
     * @param string $description
     * @return string The URL of the created Pull Request.
     */
    public function createPullRequest(string $sourceBranch, string $targetBranch, string $title, string $description): string;
}
