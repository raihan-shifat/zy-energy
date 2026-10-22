<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Homepage "Product Categories" grid and header "Products" menu show every active
 * category (they used to stop at 7). Runs inside a transaction that is rolled back.
 */
class HomeCategoriesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        forgetCategoriesCache();
    }

    protected function tearDown(): void
    {
        forgetCategoriesCache();
        parent::tearDown();
    }

    private function addCategory(string $name): Category
    {
        $category = new Category(['slug' => \Illuminate\Support\Str::slug($name).'-'.uniqid(), 'status' => 1]);
        $category->sort_order = (int) Category::max('sort_order') + 1;
        $category->save();
        $category->translations()->create(['language_code' => 'en', 'name' => $name]);

        return $category;
    }

    /** Make exactly $n categories active (deactivating or adding as needed). */
    private function activeCount(int $n): void
    {
        $active = Category::where('status', 1)->orderBy('sort_order')->orderBy('id')->get();
        foreach ($active->slice($n) as $extra) {
            $extra->update(['status' => 0]);
        }
        for ($i = $active->count(); $i < $n; $i++) {
            $this->addCategory('Test Category '.$i);
        }
        forgetCategoriesCache();
    }

    private function counts(): array
    {
        $html = $this->get('/')->assertOk()->getContent();

        return [substr_count($html, 'class="cat-card'), substr_count($html, 'dropdown-item fw-bold mb-1')];
    }

    public function test_all_active_categories_show_for_6_to_9(): void
    {
        foreach ([6, 7, 8, 9] as $n) {
            $this->activeCount($n);
            $this->assertSame([$n, $n], $this->counts(), "homepage cards and menu entries for $n active categories");
        }
    }

    public function test_eighth_category_is_last_in_admin_order_and_hides_when_switched_off(): void
    {
        $this->activeCount(7);
        $eighth = $this->addCategory('Eighth Category');
        forgetCategoriesCache();

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('Eighth Category', $html);
        $this->assertSame(8, substr_count($html, 'class="cat-card'));

        // Switching it off in the admin clears the cache, so it disappears at once.
        $admin = User::query()->get()->first(fn (User $u) => $u->isStaff()) ?? $this->markTestSkipped('No staff user.');
        $this->actingAs($admin)->postJson(route('admin.categories.updateStatus'), ['id' => $eighth->id, 'status' => 0])->assertJson(['success' => true]);
        $this->assertStringNotContainsString('Eighth Category', $this->get('/')->getContent());
    }

    public function test_optional_limits_still_work(): void
    {
        $this->activeCount(8);
        config(['storefront.home_categories_limit' => 4, 'storefront.menu_categories_limit' => 6]);

        $this->assertSame([4, 6], $this->counts());
    }
}
