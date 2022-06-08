<?php

namespace Illuminatech\ModelRules\Test;

use Illuminate\Database\Eloquent\Collection;
use Illuminatech\ModelRules\MultiUnique;
use Illuminatech\ModelRules\Test\Support\Category;
use Illuminatech\ModelRules\Test\Support\Item;

class MultiUniqueTest extends TestCase
{
    public function testPassValidation()
    {
        $rule = new MultiUnique(Category::class);

        $unexistingId = Category::query()->max('id') * 2;

        $this->assertTrue($rule->passes('category_ids', [$unexistingId]));

        $this->assertCount(0, $rule->getModels());
    }

    public function testFailValidation()
    {
        $rule = new MultiUnique(Category::class);

        $existingIds = Category::query()->pluck('id');

        $this->assertFalse($rule->passes('category_ids', $existingIds));

        $models = $rule->getModels();
        $this->assertTrue($models instanceof Collection);
        $this->assertTrue($models[0] instanceof Category);
        $this->assertSame(count($existingIds), $models->count());
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testResetModels()
    {
        $rule = new MultiUnique(Category::class);

        $existingId = Category::query()->max('id');

        $this->assertFalse($rule->passes('category_id', [$existingId]));
        $this->assertCount(1, $rule->getModels());

        $this->assertTrue($rule->passes('category_ids', [$existingId * 2]));
        $this->assertCount(0, $rule->getModels());
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testCustomAttribute()
    {
        $rule = new MultiUnique(Category::class, 'name');

        $existingName = Category::query()->firstOrFail()->name;

        $this->assertTrue($rule->passes('category_names', ['unexisting-category-name']));
        $this->assertFalse($rule->passes('category_names', [$existingName]));
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testCustomQuery()
    {
        $category = Category::query()
            ->orderBy('id', 'desc')
            ->firstOrFail();

        $invalidId = $category->items()->firstOrFail()->id;

        $validId = Item::query()
            ->where('category_id', '!=', $category->id)
            ->firstOrFail()
            ->id;

        $rule = new MultiUnique($category->items());

        $this->assertTrue($rule->passes('item_ids', [$validId]));
        $this->assertFalse($rule->passes('item_ids', [$invalidId]));
    }
}