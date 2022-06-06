<p align="center">
    <a href="https://github.com/illuminatech" target="_blank">
        <img src="https://avatars1.githubusercontent.com/u/47185924" height="100px">
    </a>
    <h1 align="center">Laravel Model Validation Rules</h1>
    <br>
</p>

This extension provides set of the validation rules to check if a model exists or is unique for Laravel.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/illuminatech/model-rules.svg)](https://packagist.org/packages/illuminatech/model-rules)
[![Total Downloads](https://img.shields.io/packagist/dt/illuminatech/model-rules.svg)](https://packagist.org/packages/illuminatech/model-rules)
[![Build Status](https://github.com/illuminatech/model-rules/workflows/build/badge.svg)](https://github.com/illuminatech/model-rules/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist illuminatech/model-rules
```

or add

```json
"illuminatech/model-rules": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension provides set of the validation rules to check if a model exists or is unique for Laravel.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'name' => [
                'required',
                'string',
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id', // executes SQL query 'SELECT ... FROM categories WHERE id =...'
            ],
        ]);
        
        $item = new Item();
        $item->name = $validatedData['name'];
        
        $category = Category::query()->find($validatedData['category_id']); // executes another SQL query 'SELECT ... FROM categories WHERE id =...'
        $item->category()->associate($category);
        
        $item->save();
        // ...
    }
}
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminatech\ModelRules\Exists;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'name' => [
                'required',
                'string',
            ],
            'category_id' => [
                'required',
                'integer',
                $categoryRule = new Exists(Category::class), // executes SQL query 'SELECT ... FROM categories WHERE id =...' only once
            ],
        ]);
        
        $item = new Item();
        $item->name = $validatedData['name'];
        
        $category = $categoryRule->getModel(); // returns model fetched during validation without extra SQL query
        $item->category()->associate($category);
        
        $item->save();
        // ...
    }
}
```