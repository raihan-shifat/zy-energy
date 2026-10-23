<?php

namespace Tests\Feature;

use App\Models\AboutMedia;
use App\Models\AboutSection;
use App\Models\User;
use App\Services\AboutPageService;
use Database\Seeders\AboutPageSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * About Us editor (admin), uploads and the public /about page.
 * Runs against the configured database inside a transaction that is rolled back.
 */
class AdminAboutPageTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::query()->get()->first(fn (User $u) => $u->isStaff())
            ?? $this->markTestSkipped('No staff user in the database.');
        Storage::fake('public');
        AboutPageService::clearCache();
    }

    protected function tearDown(): void
    {
        AboutPageService::clearCache();
        parent::tearDown();
    }

    private function uploadImage(string $name = 'a.jpg', int $w = 2400, int $h = 1600): string
    {
        return $this->actingAs($this->admin)
            ->post(route('admin.about.upload-image'), ['upload' => UploadedFile::fake()->image($name, $w, $h)])
            ->assertOk()->json('path');
    }

    private function save(array $sections)
    {
        return $this->actingAs($this->admin)->putJson(route('admin.about.update'), ['sections' => $sections]);
    }

    public function test_seeded_sections_reproduce_the_old_page(): void
    {
        $this->assertGreaterThanOrEqual(5, AboutSection::count());
        $html = $this->get('/about')->assertOk()->getContent();
        foreach (['Who We Are', 'Our Mission', 'Quality without compromise', 'Why Partner With ZY Energy', 'OEM &amp; ODM Manufacturing', 'Ready to start a project?'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->get('/zh/about')->assertOk()->assertSee('关于我们')->assertSee('我们的使命');

        // Running the seeder again changes nothing.
        $count = AboutSection::count();
        (new AboutPageSeeder)->run();
        $this->assertSame($count, AboutSection::count());
    }

    public function test_editor_page_lists_sections_and_templates(): void
    {
        $this->actingAs($this->admin)->get(route('admin.about.edit'))
            ->assertOk()
            ->assertSee('id="ab-tpl-gallery"', false)
            ->assertSee('id="ab-tpl-video"', false)
            ->assertSee('extraPlugins: [ZyEditorUploadPlugin]', false)
            ->assertSee(json_encode(route('admin.about.upload-video')), false);
    }

    public function test_image_upload_is_resized_and_type_checked(): void
    {
        $path = $this->uploadImage('wide.jpg', 3000, 1500);
        $this->assertStringStartsWith('about/', $path);
        [$w] = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame(1920, $w);

        $this->actingAs($this->admin)->postJson(route('admin.about.upload-image'), ['upload' => UploadedFile::fake()->image('anim.gif')])
            ->assertStatus(422)->assertJsonPath('error.message', __('cms.about_editor.image_bad_type'));
    }

    /** A real uploaded file (UploadedFile::fake() reports its MIME type from the name, not the content). */
    private function realUpload(string $name, string $content): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'vid');
        file_put_contents($tmp, $content);

        return new UploadedFile($tmp, $name, null, null, true);
    }

    public function test_video_upload_checks_content_type_on_the_server(): void
    {
        // A text file renamed to .mp4 is refused by its content.
        $this->actingAs($this->admin)->postJson(route('admin.about.upload-video'), ['upload' => $this->realUpload('clip.mp4', 'not really a video')])
            ->assertStatus(422)->assertJsonPath('error.message', __('cms.about_editor.video_bad_type'));

        // Minimal MP4 header (ftyp box) is recognised as an MP4 video.
        $mp4 = hex2bin('00000018667479706973'.'6f6d0000020069736f6d69736f32').str_repeat("\0", 64);
        $ok = $this->actingAs($this->admin)->post(route('admin.about.upload-video'), ['upload' => $this->realUpload('clip.mp4', $mp4)]);
        $ok->assertOk();
        $this->assertStringStartsWith('about/videos/', $ok->json('path'));
    }

    public function test_save_reorders_disables_adds_and_removes_sections(): void
    {
        $existing = AboutSection::orderBy('sort_order')->get();
        [$hero, $text] = [$existing[0], $existing[1]];
        $gallery = [$this->uploadImage('g1.jpg', 800, 600), $this->uploadImage('g2.png', 600, 800)];

        $this->save([
            ['id' => $text->id, 'type' => 'text', 'is_active' => 0, 'tr' => ['en' => ['title' => 'Hidden text', 'body' => '<p>x</p>']]],
            ['id' => $hero->id, 'type' => 'hero', 'is_active' => 1, 'tr' => ['en' => ['title' => 'Hero EN', 'subtitle' => 'Lead'], 'zh' => ['title' => '英雄标题']]],
            ['type' => 'stats', 'is_active' => 1, 'items' => [['number' => '15+'], ['number' => '50']],
                'tr' => ['en' => ['title' => 'In numbers', 'items' => [['label' => 'Years'], ['label' => 'Countries']]]]],
            ['type' => 'gallery', 'is_active' => 1, 'tr' => ['en' => ['title' => 'Factory']],
                'media' => [['kind' => 'image', 'path' => $gallery[1], 'tr' => ['en' => ['alt' => 'Second', 'caption' => 'Line B']]],
                            ['kind' => 'image', 'path' => $gallery[0], 'tr' => ['en' => ['alt' => 'First']]]]],
            ['type' => 'video', 'is_active' => 1, 'tr' => ['en' => ['title' => 'Tour']],
                'media' => [['kind' => 'video', 'url' => 'https://youtu.be/dQw4w9WgXcQ'], ['kind' => 'poster', 'path' => null]]],
        ])->assertOk()->assertJson(['message' => __('cms.about_editor.saved')]);

        $sections = AboutSection::orderBy('sort_order')->get();
        $this->assertSame(['text', 'hero', 'stats', 'gallery', 'video'], $sections->pluck('type')->all());
        $this->assertFalse($sections[0]->is_active);
        $this->assertSame(['15+', '50'], array_column($sections[2]->settings['items'], 'number'));
        $this->assertSame([$gallery[1], $gallery[0]], $sections[3]->media->pluck('path')->all());
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $sections[4]->media->first()->url);
        $this->assertNull(AboutSection::find($existing[2]->id), 'sections left out are removed');

        // Public page: order, hidden section skipped, stats, gallery caption + alt, lazy video iframe; cache cleared on save.
        $html = $this->get('/about')->assertOk()->getContent();
        $this->assertStringNotContainsString('Hidden text', $html);
        $this->assertTrue(strpos($html, 'Hero EN') < strpos($html, 'In numbers') && strpos($html, 'In numbers') < strpos($html, 'Factory'));
        $this->assertStringContainsString('data-ab-count="15+"', $html);
        $this->assertStringContainsString('alt="Second"', $html);
        $this->assertStringContainsString('Line B', $html);
        $this->assertMatchesRegularExpression('#<iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"[^>]*loading="lazy"#', $html);
        $this->get('/zh/about')->assertSee('英雄标题');
    }

    public function test_removed_gallery_images_keep_their_files(): void
    {
        $path = $this->uploadImage('keep.jpg', 800, 600);
        $this->save([['type' => 'gallery', 'is_active' => 1, 'tr' => ['en' => ['title' => 'G']], 'media' => [['kind' => 'image', 'path' => $path]]]])->assertOk();
        $this->save([['id' => AboutSection::value('id'), 'type' => 'gallery', 'is_active' => 1, 'tr' => ['en' => ['title' => 'G']], 'media' => []]])->assertOk();

        $this->assertSame(0, AboutMedia::count());
        Storage::disk('public')->assertExists($path);
    }

    public function test_validation_errors_are_clear(): void
    {
        $this->save([['type' => 'cta', 'is_active' => 1, 'settings' => ['button_link' => 'javascript:alert(1)'], 'tr' => ['en' => ['title' => 'x']]]])
            ->assertStatus(422)->assertJsonFragment([__('cms.about_editor.link_invalid')]);

        $this->save([['type' => 'video', 'is_active' => 1, 'media' => [['kind' => 'video', 'url' => 'https://example.com/movie']]]])
            ->assertStatus(422);
        $this->assertStringContainsString(__('cms.about_editor.video_url_invalid'), json_encode($this->save([['type' => 'video', 'is_active' => 1, 'media' => [['kind' => 'video', 'url' => 'https://example.com/movie']]]])->json('errors'), JSON_UNESCAPED_UNICODE));

        $thirteen = array_fill(0, 13, ['kind' => 'image', 'path' => 'about/x.jpg']);
        $this->save([['type' => 'gallery', 'is_active' => 1, 'media' => $thirteen]])->assertStatus(422);

        $this->save([['type' => 'gallery', 'is_active' => 1, 'media' => [['kind' => 'image', 'path' => '../.env']]]])
            ->assertStatus(422)->assertJsonFragment([__('cms.about_editor.media_invalid')]);

        $this->save([['type' => 'gallery', 'is_active' => 1, 'media' => [['kind' => 'image', 'path' => 'about/missing.jpg']]]])
            ->assertStatus(422);
    }

    public function test_embed_url_parser(): void
    {
        foreach ([
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ?t=10' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://vimeo.com/76979871' => 'https://player.vimeo.com/video/76979871',
            'https://player.vimeo.com/video/76979871' => 'https://player.vimeo.com/video/76979871',
            'https://example.com/x' => null,
            'javascript:alert(1)' => null,
        ] as $in => $out) {
            $this->assertSame($out, AboutPageService::embedUrl($in), $in);
        }
    }
}
