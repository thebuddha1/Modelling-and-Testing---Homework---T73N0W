<?php

namespace Tests\Feature\ModelBased;

use App\Models\User;
use App\Models\Category;
use App\Models\Forum;

class PostAdapter
{
    protected $test;
    protected $user;
    protected $category;
    protected $post;
    protected $switchStreak = 0;
    protected $hasSaved = false;
    protected $hasLiked = false;
    protected $hasDisliked = false;
    protected $hasEdited = false;
    protected $inEditPage = false;
    protected $exists = true;
    public function __construct($test)
    {
        $this->test = $test;
        $this->user = User::factory()->create();
        $this->test->actingAs($this->user);
        $this->category = Category::factory()->create();
        $this->post = Forum::create([
            'name' => 'Test Post',
            'content' => 'Test content',
            'category_id' => $this->category->id,
            'owner' => $this->user->id
        ]);
        $this->post->impression()->create([
            'likes' => 0,
            'dislikes' => 0,
        ]);
        $this->post->report()->create([
            'count' => 0,
        ]);
        $this->resetState();
    }

    protected function resetState()
    {
        $this->hasLiked = false;
        $this->hasDisliked = false;
        $this->hasSaved = false;
        $this->switchStreak = 0;
        $this->hasEdited = false;

        $this->inEditPage = false;
        $this->exists = true;
    }

    public function e_reset()
    {
        $this->resetState();

        $this->test->actingAs($this->user);
    }

    public function e_likePost()
    {
        if (!$this->exists || $this->switchStreak != 0) return;

        $this->test->postJson(
            "/forum/{$this->post->id}/impressions/save",
            ['newState' => 'like']
        )->assertStatus(200);

        $this->hasLiked = true;
    }

    public function e_dislikePost()
    {
        if (!$this->exists || $this->switchStreak != 0) return;

        $this->test->postJson(
            "/forum/{$this->post->id}/impressions/save",
            ['newState' => 'dislike']
        )->assertStatus(200);

        $this->hasDisliked = true;
    }

    public function e_removeLike()
    {
        if (!$this->hasLiked) return;

        $this->test->postJson(
            "/forum/{$this->post->id}/impressions/save",
            ['newState' => 'none']
        )->assertStatus(200);

        $this->hasLiked = false;
    }

    public function e_removeDislike()
    {
        if (!$this->hasDisliked) return;

        $this->test->postJson(
            "/forum/{$this->post->id}/impressions/save",
            ['newState' => 'none']
        )->assertStatus(200);

        $this->hasDisliked = false;
    }

    public function e_switchToLike()
    {
        if (!$this->hasDisliked || $this->switchStreak >= 2) return;

        $this->test->postJson(
            "/forum/{$this->post->id}/impressions/save",
            ['newState' => 'like']
        )->assertStatus(200);

        $this->hasDisliked = false;
        $this->hasLiked = true;
        $this->switchStreak++;
    }

    public function e_switchToDislike()
    {
        if (!$this->hasLiked || $this->switchStreak >= 2) return;

        $this->test->postJson(
            "/forum/{$this->post->id}/impressions/save",
            ['newState' => 'dislike']
        )->assertStatus(200);

        $this->hasLiked = false;
        $this->hasDisliked = true;
        $this->switchStreak++;
    }

    public function e_comment()
    {
        if (!$this->exists) return;

        $this->test->post(
            "/forum/{$this->post->id}/comments",
            ['content' => 'Test comment']
        )->assertStatus(302);

        $this->switchStreak = 0;
    }

    public function e_reportPost()
    {
        if (!$this->exists) return;

        $this->test->post(
            "/forum/{$this->post->id}/report"
        )->assertStatus(302);

        $this->switchStreak = 0;
    }

    public function e_savePost()
    {
        if ($this->hasSaved) return;

        $this->test->post(
            route('forum.save', $this->post->id)
        )->assertStatus(302);

        $this->hasSaved = true;
        $this->switchStreak = 0;
    }

    public function e_unsavePost()
    {
        if (!$this->hasSaved) return;

        $this->test->post(
            route('forum.unsave', $this->post->id)
        )->assertStatus(302);

        $this->hasSaved = false;
        $this->switchStreak = 0;
    }

    public function e_editPost()
    {
        if (!$this->exists) return;

        $this->inEditPage = true;
        $this->hasEdited = false;
        $this->switchStreak = 0;
    }

    public function e_edit()
    {
        if (!$this->inEditPage || $this->hasEdited) return;

        $this->hasEdited = true;
    }

    public function e_confirmEdit()
    {
        if (!$this->inEditPage || !$this->hasEdited) return;

        $this->test->put(
            "/forum/post/{$this->post->id}",
            [
                'name' => 'Updated ' . rand(),
                'content' => 'Updated content'
            ]
        )->assertStatus(302);

        $this->hasEdited = false;
        $this->inEditPage = false;
    }

    public function e_DeletePost()
    {
        $this->exists = false;
    }

    public function e_newPost()
    {
        if ($this->exists) return;

        $this->resetState();
    }

    public function runInputSequence(array $inputs)
    {
        foreach ($inputs as $input) {
            $method = $this->mapInputToMethod($input);

            if (!method_exists($this, $method)) {
                throw new \Exception("Missing mapping: $input → $method");
            }

            $this->$method();
        }
    }

    protected function mapInputToMethod(string $input): string
    {
        return match ($input) {
            '<reset>' => 'e_reset',
            'like post' => 'e_likePost',
            'dislike post' => 'e_dislikePost',
            'remove like' => 'e_removeLike',
            'remove dislike' => 'e_removeDislike',
            'switch to like' => 'e_switchToLike',
            'switch to dislike' => 'e_switchToDislike',

            'add comment' => 'e_comment',
            'report post' => 'e_reportPost',

            'save post' => 'e_savePost',
            'unsave post' => 'e_unsavePost',

            'edit post' => 'e_editPost',
            'modify content' => 'e_edit',
            'confirm edit' => 'e_confirmEdit',

            'auto delete post' => 'e_DeletePost',
            'create new post' => 'e_newPost',

            default => throw new \Exception("Unknown input: $input"),
        };
    }
}