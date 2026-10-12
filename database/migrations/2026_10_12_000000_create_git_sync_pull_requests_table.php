<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('git_sync_pull_requests', function (Blueprint $table) {
            $table->id();
            $table->string('target_branch');
            $table->string('source_branch');
            $table->string('title');
            $table->string('provider'); // e.g. github, bitbucket
            $table->string('pr_url')->nullable();
            $table->enum('status', ['open', 'merged', 'closed', 'failed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('git_sync_pull_requests');
    }
};
