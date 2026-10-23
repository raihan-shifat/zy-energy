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

    private function upload(string $name = 'a.jpg'): string
    {
        return $this->actingAs($this->admin)
            ->post(route('admin.news.upload-image'), ['upload' => UploadedFile::fake()->image($name, 800, 600)])
            ->assertOk()->json('path');
    }

    private function gallery(array $items, int $primary = 0): array
    {
        return ['gallery_present' => '1', 'gallery' => $items, 'gallery_primary' => (string) $primary];
    }

    public function test_news_image_upload_endpoint_checks_type_and_size(): void
    {
        $this->assertStringStartsWith('news/', $this->upload('ok.webp'));

        $this->actingAs($this->admin)->postJson(route('admin.news.upload-image'), ['upload' => UploadedFile::fake()->image('anim.gif')])
            ->assertStatus(422)->assertJsonPath('error.message', __('cms.news.image_bad_type'));
        $this->actingAs($this->admin)->postJson(route('admin.news.upload-image'), ['upload' => UploadedFile::fake()->image('big.jpg')->size(3000)])
            ->assertStatus(422)->assertJsonPath('error.message', fn ($m) => str_contains($m, '2 MB'));
    }

    public function test_news_image_upload_returns_public_url_for_jpg_and_rejects_txt(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.news.upload-image'), ['upload' => UploadedFile::fake()->image('photo.jpg', 800, 600)])
            ->assertOk();
        $path = $response->json('path');
        $this->assertMatchesRegularExpression('#^news/[A-Za-z0-9._-]+\.jpe?g$#', $path);
        $this->assertSame(asset('storage/'.$path), $response->json('url'));
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->admin)
            ->postJson(route('admin.news.upload-image'), ['upload' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain')])
            ->assertStatus(422)->assertJsonPath('error.message', __('cms.news.image_bad_type'));
    }

    public function test_multiple_images_cover_reorder_remove_and_keep(): void
    {
        [$a, $b, $c] = [$this->upload('a.jpg'), $this->upload('b.png'), $this->upload('c.webp')];
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload() + $this->gallery([['path' => $a], ['path' => $b], ['path' => $c]], 1))
            ->assertRedirect(route('admin.news.index'));

        $news = News::with('images')->latest('id')->first();
        $this->assertSame([$a, $b, $c], $news->images->pluck('path')->all());
        $this->assertSame($b, $news->coverPath());
        $this->assertSame($b, $news->image_url, 'old column follows the cover');

        // The edit page lists the existing images in the manager.
        $this->actingAs($this->admin)->get(route('admin.news.edit', $news->id))->assertOk()
            ->assertSee(json_encode(asset('storage/'.$c)), false);

        // Reorder (c, a), remove b, add d, cover = d.
        $ids = $news->images->pluck('id', 'path');
        $d = $this->upload('d.jpg');
        $this->actingAs($this->admin)->put(route('admin.news.update', $news->id), $this->payload()
            + $this->gallery([['id' => $ids[$c]], ['id' => $ids[$a]], ['path' => $d]], 2))->assertRedirect();
        $news = $news->fresh('images');
        $this->assertSame([$c, $a, $d], $news->images->pluck('path')->all());
        $this->assertSame($d, $news->coverPath());
        Storage::disk('public')->assertMissing($b);   // not used anywhere else

        // Saving without the image list (old cached form) keeps every image.
        $this->actingAs($this->admin)->put(route('admin.news.update', $news->id), $this->payload())->assertRedirect();
        $this->assertCount(3, $news->fresh()->images);

        // Max 8 images.
        $nine = array_map(fn ($i) => ['path' => 'news/x'.$i.'.jpg'], range(1, 9));
        $this->actingAs($this->admin)->put(route('admin.news.update', $news->id), $this->payload() + $this->gallery($nine))
            ->assertSessionHasErrors(['gallery' => __('cms.news.gallery_too_many', ['max' => 8])]);
    }

    public function test_removed_image_file_is_kept_when_used_elsewhere(): void
    {
        $shared = $this->upload('shared.jpg');
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload() + $this->gallery([['path' => $shared]]));
        $first = News::latest('id')->first();
        // Another post still points at the same file through the old column.
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload());
        News::latest('id')->first()->forceFill(['image_url' => $shared])->save();

        $this->actingAs($this->admin)->put(route('admin.news.update', $first->id), $this->payload() + $this->gallery([]))->assertRedirect();
        $this->assertCount(0, $first->fresh()->images);
        Storage::disk('public')->assertExists($shared);
    }

    public function test_public_gallery_and_list_placeholder(): void
    {
        [$a, $b, $c] = [$this->upload('a.jpg'), $this->upload('b.jpg'), $this->upload('c.jpg')];
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload() + $this->gallery([['path' => $a], ['path' => $b], ['path' => $c]]));
        $news = News::latest('id')->first();

        $html = $this->get(route('news.show', $news->slug))->assertOk()->getContent();
        $this->assertStringContainsString('class="news-cover', $html);
        $this->assertStringContainsString('storage/'.$a, $html);
        $this->assertSame(2, substr_count($html, 'class="news-gallery-item"'), 'cover + 2 gallery images');
        $this->assertMatchesRegularExpression('#storage/'.preg_quote($b, '#').'"[^>]*loading="lazy"#', $html);

        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload(['translations' => ['en' => ['title' => 'No image post '.uniqid()]]]));
        $this->assertStringContainsString('images/product-placeholder.svg', $this->get(route('news.index'))->getContent());
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

    public function test_unwritable_storage_gives_a_clear_error_and_saves_nothing(): void
    {
        $disk = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $disk->shouldReceive('putFileAs')->andReturn(false);   // what store() gets when the folder is not writable
        $disk->shouldReceive('path')->andReturn(sys_get_temp_dir());
        $manager = \Mockery::mock(\Illuminate\Filesystem\FilesystemManager::class);
        $manager->shouldReceive('disk')->andReturn($disk);
        $this->app->instance('filesystem', $manager);
        \Illuminate\Support\Facades\Storage::clearResolvedInstance('filesystem');

        $before = News::count();
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload() + ['image_url' => UploadedFile::fake()->image('cover.jpg')])
            ->assertSessionHasErrors(['image_url' => __('cms.news.image_save_failed')]);
        $this->assertSame($before, News::count(), 'no post is created when the image cannot be saved');
    }

    public function test_legacy_zero_image_value_shows_no_broken_image(): void
    {
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload());
        $news = News::latest('id')->first();
        $news->forceFill(['image_url' => '0'])->save();   // what failed uploads used to store

        $rows = $this->actingAs($this->admin)->postJson(route('admin.news.data'), ['draw' => 1, 'start' => 0, 'length' => 500])->json('data');
        $row = collect($rows)->firstWhere('id', $news->id);
        $this->assertNull($row['image_thumb']);

        $html = $this->get(route('news.show', $news->slug))->assertOk()->getContent();
        $this->assertStringNotContainsString('storage/0"', $html);
        $this->assertStringNotContainsString('class="news-cover', $html);
        $this->assertNull($news->coverPath());
        $this->assertSame('0', $news->fresh()->getRawOriginal('image_url'), 'the stored value is left unchanged');
    }

    public function test_excerpt_equal_to_body_is_shown_once(): void
    {
        $text = 'Zhongshan Zhanyu Xinghua Lighting '.uniqid();
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload(['translations' => ['en' => ['excerpt' => $text, 'body' => '<p>'.$text.'</p>']]]));
        $html = $this->get(route('news.show', News::latest('id')->value('slug')))->getContent();
        $this->assertSame(1, substr_count($html, $text.'</'), 'text appears once in the article');
        $this->assertStringNotContainsString('<p class="lead">', $html);
    }

    public function test_public_post_follows_the_language(): void
    {
        $this->actingAs($this->admin)->post(route('admin.news.store'), $this->payload());
        $news = News::latest('id')->first();

        $this->get('/zh/news/'.$news->slug)->assertOk()->assertSee('风电场动态')->assertSee('新闻与博客');
        $this->get('/zh/news')->assertOk()->assertSee('风电场动态');
    }
}
