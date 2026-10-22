<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Admin "create product" page and its store/upload endpoints.
 * Runs against the configured database inside a transaction that is rolled back.
 */
class AdminProductCreateTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->get()->first(fn (User $u) => $u->isStaff())
            ?? $this->markTestSkipped('No staff user in the database.');

        if (! Vendor::query()->exists() || ! Category::query()->exists()) {
            $this->markTestSkipped('Needs at least one vendor and one category.');
        }

        Storage::fake('public');
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'translations' => [
                'en' => ['name' => 'Test Battery '.uniqid(), 'description' => '<p>English body</p>'],
                'zh' => ['name' => '测试电池', 'description' => '<p>中文描述</p>'],
                'ar' => ['name' => 'بطارية', 'description' => ''],
            ],
            'category_id' => Category::query()->value('id'),
            'brand_id' => '',
            'vendor_id' => Vendor::query()->value('id'),
            'series' => 'S-500',
            'type' => '',
            'variants' => [
                ['name' => 'Default', 'price' => '100', 'discount_price' => '', 'stock' => '5', 'SKU' => '', 'barcode' => '', 'weight' => '', 'dimension' => ''],
            ],
        ], $overrides);
    }

    private function store(array $payload)
    {
        return $this->actingAs($this->admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $payload);
    }

    public function test_create_page_renders_with_dropdown_labels_and_editor_upload_url(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.create'));

        $response->assertOk();
        $response->assertSee(json_encode(route('admin.editor.upload-image')), false);
        $response->assertSee('extraPlugins: [ZyEditorUploadPlugin]', false);

        // Every category and brand option must have a readable label, whatever the admin language.
        foreach (Category::with('translations')->get() as $category) {
            $this->assertNotSame('—', localized_translation_value($category->translations, 'name', '—'));
        }
        foreach (Brand::with('translations')->get() as $brand) {
            $name = $brand->translations->first()?->name;
            if ($name) {
                $response->assertSee(e($name), false);
            }
        }
    }

    public function test_product_saves_without_images_in_several_languages(): void
    {
        $payload = $this->payload();
        $response = $this->store($payload);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $product = Product::with('translations', 'variants')->latest('id')->first();
        $this->assertSame('S-500', $product->series);
        $this->assertSame($payload['translations']['en']['name'], $product->translations->firstWhere('language_code', 'en')->name);
        $this->assertSame('测试电池', $product->translations->firstWhere('language_code', 'zh')->name);
        $this->assertSame('<p>中文描述</p>', $product->translations->firstWhere('language_code', 'zh')->description);
        $this->assertCount(0, $product->images);
        $this->assertNotSame('', $product->slug);
    }

    public function test_product_saves_with_images(): void
    {
        $response = $this->store($this->payload([
            'images' => [UploadedFile::fake()->image('a.jpg', 600, 600), UploadedFile::fake()->image('b.png', 300, 300)],
        ]));

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::with('images')->latest('id')->first();
        $this->assertCount(2, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->image_url);
    }

    public function test_slug_is_unique_and_never_empty(): void
    {
        $name = 'Same Name '.uniqid();
        $this->store($this->payload(['translations' => ['en' => ['name' => $name]]]));
        $this->store($this->payload(['translations' => ['en' => ['name' => $name]]]));
        $slugs = Product::latest('id')->take(2)->pluck('slug')->all();
        $this->assertNotSame($slugs[0], $slugs[1]);

        $this->store($this->payload(['translations' => ['en' => ['name' => '!!!']]]));
        $this->assertNotSame('', Product::latest('id')->value('slug'));
    }

    public function test_only_first_of_several_variants_is_primary(): void
    {
        $this->store($this->payload(['variants' => [
            0 => ['name' => 'A', 'price' => '10', 'stock' => '1'],
            1 => ['name' => 'B', 'price' => '20', 'stock' => '2'],
            2 => ['name' => 'C', 'price' => '30', 'stock' => '3'],
        ]]))->assertRedirect(route('admin.products.index'));

        $variants = Product::latest('id')->first()->variants()->orderBy('id')->get();
        $this->assertCount(3, $variants);
        $this->assertSame([1, 0, 0], $variants->pluck('is_primary')->map(fn ($v) => (int) $v)->all());
    }

    public function test_zero_variants_is_rejected_with_a_clear_message(): void
    {
        $payload = $this->payload();
        unset($payload['variants']);

        $this->store($payload)->assertSessionHasErrors(['variants' => 'Add at least one variant.']);
    }

    public function test_variant_number_rules(): void
    {
        $this->store($this->payload(['variants' => [0 => ['price' => '-1', 'stock' => '-2', 'discount_price' => '-3']]]))
            ->assertSessionHasErrors(['variants.0.price', 'variants.0.stock', 'variants.0.discount_price']);

        $response = $this->store($this->payload(['variants' => [0 => ['price' => '50', 'discount_price' => '80']]]));
        $response->assertSessionHasErrors('variants.0.discount_price');
        $this->assertStringContainsString('Variant Discount Price', session('errors')->first('variants.0.discount_price'));
    }

    public function test_duplicate_sku_and_barcode_are_rejected(): void
    {
        $this->store($this->payload(['variants' => [
            0 => ['name' => 'A', 'price' => '10', 'stock' => '1', 'SKU' => 'DUP-1', 'barcode' => '111'],
            1 => ['name' => 'B', 'price' => '10', 'stock' => '1', 'SKU' => 'DUP-1', 'barcode' => '111'],
        ]]))->assertSessionHasErrors(['variants.0.SKU', 'variants.1.SKU', 'variants.0.barcode', 'variants.1.barcode']);
    }

    public function test_weight_with_unit_gives_a_readable_error_and_series_is_length_checked(): void
    {
        $response = $this->store($this->payload([
            'series' => str_repeat('x', 300),
            'variants' => [0 => ['weight' => '1.5 kg']],
        ]));

        $response->assertSessionHasErrors(['series', 'variants.0.weight']);
        $this->assertStringContainsString('Variant Weight', session('errors')->first('variants.0.weight'));
    }

    public function test_missing_english_name_error_names_the_field(): void
    {
        $this->store($this->payload(['translations' => ['en' => ['name' => '']]]))
            ->assertSessionHasErrors('translations.en.name');

        $this->assertStringContainsString('Name (English)', session('errors')->first('translations.en.name'));
    }

    public function test_product_without_image_shows_a_placeholder_on_the_storefront(): void
    {
        $html = getResponsiveImageHtml('default.jpg', ['alt' => 'X', 'class' => 'img-fluid']);

        $this->assertStringContainsString('images/product-placeholder.svg', $html);
        $this->assertStringNotContainsString('storage/default.jpg', $html);
    }

    public function test_editor_image_upload_returns_a_public_url(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.editor.upload-image'), [
            'upload' => UploadedFile::fake()->image('inline.png', 400, 300),
        ]);

        $response->assertOk()->assertJsonStructure(['url']);
        $path = str_replace(asset('storage').'/', '', $response->json('url'));
        Storage::disk('public')->assertExists($path);
    }

    public function test_editor_image_upload_rejects_non_images_and_guests(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.editor.upload-image'), ['upload' => UploadedFile::fake()->create('evil.php', 1, 'text/x-php')])
            ->assertStatus(422)
            ->assertJsonPath('error.message', fn ($m) => is_string($m) && $m !== '');

        auth()->logout();
        $this->post(route('admin.editor.upload-image'), ['upload' => UploadedFile::fake()->image('x.png')])
            ->assertRedirect();
    }
}
