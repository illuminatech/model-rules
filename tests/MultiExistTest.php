<?php

namespace Illuminatech\ModelRules\Test;

use Illuminate\Database\Eloquent\Collection;
use Illuminatech\ModelRules\MultiExist;
use Illuminatech\ModelRules\Test\Support\Category;
use Illuminatech\ModelRules\Test\Support\Item;

class MultiExistTest extends TestCase
{
    public function testPassValidation()
    {
        $rule = new MultiExist(Category::class);

        $validIds = Category::query()->pluck('id');

        $this->assertTrue($rule->passes('category_ids', $validIds));

        $models = $rule->getModels();
        $this->assertTrue($models instanceof Collection);
        $this->assertTrue($models[0] instanceof Category);
        $this->assertSame(count($validIds), $models->count());
    }

    public function testFailValidation()
    {
        $rule = new MultiExist(Category::class);

        $invalidId = Category::query()->max('id') * 2;

        $this->assertFalse($rule->passes('category_ids', [$invalidId]));

        $this->assertCount(0, $rule->getModels());
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testResetModels()
    {
        $rule = new MultiExist(Category::class);

        $validId = Category::query()->max('id');

        $this->assertTrue($rule->passes('category_ids', [$validId]));
        $this->assertCount(1, $rule->getModels());

        $this->assertFalse($rule->passes('category_id', [$validId * 2]));
        $this->assertCount(0, $rule->getModels());
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testCustomAttribute()
    {
        $rule = new MultiExist(Category::class, 'name');

        $validName = Category::query()->firstOrFail()->name;

        $this->assertTrue($rule->passes('category_name', [$validName]));
        $this->assertFalse($rule->passes('category_name', ['unexisting-category-name']));
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

        $validId = $category->items()->firstOrFail()->id;

        $invalidId = Item::query()
            ->where('category_id', '!=', $category->id)
            ->firstOrFail()
            ->id;

        $rule = new MultiExist($category->items());

        $this->assertTrue($rule->passes('item_id', [$validId]));
        $this->assertFalse($rule->passes('item_id', [$invalidId]));
    }
}