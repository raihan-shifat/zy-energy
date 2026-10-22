<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * News & Blog admin (create/edit/status) and the public news pages.
 * Runs against the configured database inside a transaction that is rolled back.
 */
class AdminNewsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::query()->get()->first(fn (User $u) => $u->isStaff())
            ?? $this->markTestSkipped('No staff user in the database.');
        Storage::fake('public');
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'translations' => [
                'en' => ['title' => 'Wind Farm Update '.uniqid(), 'excerpt' => 'Short summary', 'body' => '<p>English body</p><figure class="image"><img src="https://zzyenergy.com/storage/editor/2026/10/x.jpg"></figure>'],
                'zh' => ['title' => '风电场动态', 'excerpt' => '', 'body' => '<p>中文正文</p>'],
            ],
            'status' => 'on',
        ], $overrides);
    }

    public function test_labels_are_renamed_to_news_and_blog(): void
    {
        $this->actingAs($this->admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('Add Post')
            ->assertSee('News &amp; Blog', false)              // sidebar
            ->assertSee('extraPlugins: [ZyEditorUploadPlugin]', false)
            ->assertSee('accept="image/jpeg,image/png,image/webp"', false)
            ->assertSee('maxlength="500"', false)
            ->assertSee('data-bs-target="#lang-en"', false);

        $this->get(route('news.index'))->assertOk()->assertSee('News &amp; Blog', false);
    }

    public function test_create_with_cover_image_and_two_languages(): void
    {
        $payload = $this->payload() + ['image_url' => UploadedFile::fake()->image('cover.jpg', 1200, 800)];

        $this->actingAs($this->admin)->post(route('admin.news.store'), $payload)
            ->assertRedirect(route('admin.news.index'))
            ->assertSessionHas('success', 'Post created successfully.');

        $news = News::with('translations')->latest('id')->first();
        Storage::disk('public')->assertExists($news->image_url);
        $this->assertStringStartsWith('news/', $news->image_url);
        $this->assertSame('风电场动态', $news->translations->firstWhere('language_code', 'zh')->title);
        $this->assertStringContainsString('storage/editor/2026/10/x.jpg', $news->translations->firstWhere('language_code', 'en')->body);
        $this->assertStringStartsWith('wind-farm-update-', $news->slug);

        // Public page: cover image in the fixed box with the post title as alt, body image kept.
        $html = $this->get(route('news.show', $news->slug))->assertOk()->getContent();
        $this->assertStringContainsString('class="news-cover', $html);
        $this->assertStringContainsString('storage/'.$news->image_url, $html);
        $this->assertStringContainsString('alt="'.e($payload['translations']['en']['title']).'"', $html);
        $this->assertStringContainsString('storage/editor/2026/10/x.jpg', $html);
    }

    public function test_image_type_and_size_errors_are_clear(): void
    {
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload() + ['image_url' => UploadedFile::fake()->image('anim.gif')])
            ->assertSessionHasErrors(['image_url' => __('cms.news.image_bad_type')]);

        $tmp = tempnam(sys_get_temp_dir(), 'big');
        file_put_contents($tmp, 'x');
        $tooBig = new UploadedFile($tmp, 'huge.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload() + ['image_url' => $tooBig])
            ->assertSessionHasErrors('image_url');
        $this->assertStringContainsString('must not be larger than', session('errors')->first('image_url'));
    }

    public function test_title_and_body_are_required_in_english_with_readable_messages(): void
    {
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload(['translations' => ['en' => ['title' => '', 'body' => '']]]))
            ->assertSessionHasErrors(['translations.en.title', 'translations.en.body']);

        $errors = session('errors');
        $this->assertStringContainsString('Title (en)', $errors->first('translations.en.title'));
        $this->assertStringContainsString('Body (en)', $errors->first('translations.en.body'));

        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload(['translations' => ['en' => ['excerpt' => str_repeat('x', 501)]]]))
            ->assertSessionHasErrors('translations.en.excerpt');
    }

    public function test_edit_keeps_or_replaces_the_image(): void
    {
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload() + ['image_url' => UploadedFile::fake()->image('a.png')]);
        $news = News::latest('id')->first();
        $original = $news->image_url;

        $this->actingAs($this->admin)->get(route('admin.news.edit', $news->id))
            ->assertOk()
            ->assertSee('storage/'.$original, false)
            ->assertSee(__('cms.news.image_current_hint'));

        // No new file: the image stays.
        $this->actingAs($this->admin)->put(route('admin.news.update', $news->id), $this->payload())
            ->assertRedirect(route('admin.news.index'))->assertSessionHas('success', 'Post updated successfully.');
        $this->assertSame($original, $news->fresh()->image_url);

        // New file: replaced.
        $this->actingAs($this->admin)->put(route('admin.news.update', $news->id), $this->payload() + ['image_url' => UploadedFile::fake()->image('b.webp')]);
        $this->assertNotSame($original, $news->fresh()->image_url);
        Storage::disk('public')->assertExists($news->fresh()->image_url);
    }

    public function test_slug_is_unique_and_survives_non_latin_titles(): void
    {
        $title = 'Same Title '.uniqid();
        foreach ([1, 2] as $_) {
            $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload(['translations' => ['en' => ['title' => $title]]]));
        }
        $slugs = News::latest('id')->take(2)->pluck('slug');
        $this->assertNotSame($slugs[0], $slugs[1]);

        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload(['translations' => ['en' => ['title' => '!!! ???']]]));
        $this->assertNotSame('', News::latest('id')->value('slug'));
    }

    public function test_status_off_hides_the_post_publicly(): void
    {
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload());
        $news = News::latest('id')->first();
        $this->get(route('news.show', $news->slug))->assertOk();

        $this->actingAs($this->admin)->postJson(route('admin.news.updateStatus'), ['id' => $news->id, 'status' => 0])
            ->assertJson(['success' => true]);
        $this->get(route('news.show', $news->slug))->assertNotFound();
        $this->get(route('news.index'))->assertDontSee($news->slug);
    }

    public function test_public_post_follows_the_language(): void
    {
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload());
        $news = News::latest('id')->first();

        $this->get('/zh/news/'.$news->slug)->assertOk()->assertSee('风电场动态')->assertSee('新闻与博客');
        $this->get('/zh/news')->assertOk()->assertSee('风电场动态');
    }
}
