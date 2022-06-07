<?php

namespace Illuminatech\ModelRules\Test;

use Illuminatech\ModelRules\Exists;
use Illuminatech\ModelRules\Test\Support\Category;
use Illuminatech\ModelRules\Test\Support\Item;

class ExistsTest extends TestCase
{
    public function testPassValidation()
    {
        $rule = new Exists(Category::class);

        $validId = Category::query()->max('id');

        $this->assertTrue($rule->passes('category_id', $validId));

        $model = $rule->getModel();
        $this->assertTrue($model instanceof Category);
        $this->assertEquals($validId, $model->id);
    }

    public function testFailValidation()
    {
        $rule = new Exists(Category::class);

        $validId = Category::query()->max('id');

        $this->assertFalse($rule->passes('category_id', $validId * 2));

        $this->assertNull($rule->getModel());
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testResetModel()
    {
        $rule = new Exists(Category::class);

        $validId = Category::query()->max('id');

        $this->assertTrue($rule->passes('category_id', $validId));
        $this->assertNotNull($rule->getModel());

        $this->assertFalse($rule->passes('category_id', $validId * 2));
        $this->assertNull($rule->getModel());
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testCustomAttribute()
    {
        $rule = new Exists(Category::class, 'name');

        $validName = Category::query()->firstOrFail()->name;

        $this->assertTrue($rule->passes('category_name', $validName));
        $this->assertFalse($rule->passes('category_name', 'unexisting-category-name'));
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

        $rule = new Exists($category->items());

        $this->assertTrue($rule->passes('item_id', $validId));
        $this->assertFalse($rule->passes('item_id', $invalidId));
    }
}