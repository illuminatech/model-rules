<?php

namespace Illuminatech\ModelRules\Test;

use Illuminatech\ModelRules\Test\Support\Category;
use Illuminatech\ModelRules\Test\Support\Item;
use Illuminatech\ModelRules\Unique;

class UniqueTest extends TestCase
{
    public function testPassValidation()
    {
        $rule = new Unique(Category::class);

        $existingId = Category::query()->max('id');

        $this->assertTrue($rule->passes('category_id', $existingId * 2));

        $this->assertNull($rule->getModel());
    }

    public function testFailValidation()
    {
        $rule = new Unique(Category::class);

        $existingId = Category::query()->max('id');

        $this->assertFalse($rule->passes('category_id', $existingId));

        $model = $rule->getModel();
        $this->assertTrue($model instanceof Category);
        $this->assertEquals($existingId, $model->id);
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testIgnore()
    {
        $model = Category::query()
            ->orderBy('id', 'asc')
            ->firstOrFail();

        $rule = new Unique(Category::class);
        $rule->ignore($model);

        $this->assertTrue($rule->passes('category_id', $model->id));
        $this->assertFalse($rule->passes('category_id', $model->id + 1));
        $this->assertTrue($rule->passes('category_id', $model->id * 1000));
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testResetModel()
    {
        $rule = new Unique(Category::class);

        $existingId = Category::query()->max('id');

        $this->assertFalse($rule->passes('category_id', $existingId));
        $this->assertNotNull($rule->getModel());

        $this->assertTrue($rule->passes('category_id', $existingId * 2));
        $this->assertNull($rule->getModel());
    }

    /**
     * @depends testPassValidation
     * @depends testFailValidation
     */
    public function testCustomAttribute()
    {
        $rule = new Unique(Category::class, 'name');

        $existingName = Category::query()->firstOrFail()->name;

        $this->assertTrue($rule->passes('category_name', 'unexisting-category-name'));
        $this->assertFalse($rule->passes('category_name', $existingName));
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

        $rule = new Unique($category->items());

        $this->assertTrue($rule->passes('item_id', $validId));
        $this->assertFalse($rule->passes('item_id', $invalidId));
    }

    /**
     * @depends testFailValidation
     */
    public function testCustomMessage()
    {
        $rule = new Unique(Category::class);
        $rule->withMessage('custom message :model_id');

        $existingId = Category::query()->max('id');

        $this->assertFalse($rule->passes('category_id', $existingId));

        $this->assertSame('custom message ' . $existingId, $rule->message());
    }
}