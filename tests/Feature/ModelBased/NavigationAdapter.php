<?php

namespace Tests\Feature\ModelBased;

use App\Models\User;
use App\Models\Forum;
use App\Models\Category;

class NavigationAdapter
{
    protected $test;
    protected $user;
    protected $category;
    protected $post;
    protected $postData;

    public function __construct($test)
    {
        $this->test = $test;

        $this->user = User::factory()->create();

        $this->category = Category::factory()->create([
            'name' => 'Test Category'
        ]);
       
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
    }

    public function e_login()
    {
        $this->test->actingAs($this->user);
    }

    protected function e_logout()
    {
        $this->test->post('/logout')->assertStatus(302);
    }

    public function e_logoutFromMain() { $this->e_logout(); }
    public function e_logoutFromPost() { $this->e_logout(); }
    public function e_logoutFromList() { $this->e_logout(); }

    public function e_openMain()
    {
        $this->test->get('/forum')->assertStatus(200);
    }

    public function e_openCategory()
    {
        $this->test->get(route('forum.show', ['category' => $this->category->id]))
            ->assertStatus(200);
    }

    public function e_openPost()
    {
        $this->test->get("/forum/post/{$this->post->id}")
            ->assertStatus(200);
    }

    public function e_openPreviewPost()
    {
        $this->e_openPost();
    }


    public function e_createPost()
    {
        $this->test->get("/forum/{$this->category->id}/create")
            ->assertStatus(200);
    }

    public function e_fillOutForm()
    {
        $this->postData = [
            'name' => 'Post ' . rand(1, 1000),
            'content' => 'Generated content'
        ];
    }

    public function e_confirmCreate()
    {
        $response = $this->test->post(
            "/forum/{$this->category->id}",
            $this->postData
        );

        $response->assertStatus(302);

        $this->post = Forum::latest()->first();
    }

    public function e_cancelCreate()
    {
        $this->test->get("/forum/category/{$this->category->id}")
            ->assertStatus(200);
    }

    public function e_backToMain()
    {
        $this->test->get('/forum')->assertStatus(200);
    }

    public function e_backToCategory()
    {
        $this->test->get("/forum/category/{$this->category->id}")
            ->assertStatus(200);
    }

    public function e_categoryBackToMain()
    {
        $this->e_backToMain();
    }

    public function e_postBackToMain()
    {
        $this->e_backToMain();
    }

    protected function resetState()
    {
        auth()->logout();

        $this->user = \App\Models\User::factory()->create();

        $this->category = \App\Models\Category::factory()->create([
            'name' => 'Test Category'
        ]);

        $this->post = \App\Models\Forum::create([
            'name' => 'Reset Post',
            'content' => 'Reset content',
            'category_id' => $this->category->id,
            'owner' => $this->user->id
        ]);

        $this->post->impression()->create([
            'likes' => 0,
            'dislikes' => 0,
        ]);
    }

    public function e_reset()
    {
        $this->resetState();
    }

    public function runInputSequence(array $inputs)
    {
        foreach ($inputs as $input) {
            $method = $this->mapInputToMethod($input);

            if (!method_exists($this, $method)) {
                throw new \Exception("Missing mapping for input: $input → $method");
            }

            $this->$method();
        }
    }

    protected function mapInputToMethod(string $input): string
    {
        return match ($input) {
            '<reset>' => 'e_reset',
            'login' => 'e_login',
            'logout from main' => 'e_logoutFromMain',
            'logout from post' => 'e_logoutFromPost',
            'logout from list' => 'e_logoutFromList',

            'open category' => 'e_openCategory',
            'open post' => 'e_openPost',
            'open preview' => 'e_openPreviewPost',

            'back to category' => 'e_backToCategory',
            'back to main from category' => 'e_categoryBackToMain',
            'back to main from post' => 'e_postBackToMain',

            'create post' => 'e_createPost',
            'fill form' => 'e_fillOutForm',
            'submit post' => 'e_confirmCreate',
            'cancel' => 'e_cancelCreate',

            default => throw new \Exception("Unknown input: $input"),
        };
    }
}