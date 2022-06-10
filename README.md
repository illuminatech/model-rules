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
It serves 2 main purposes:

- define a fluent interface for Eloquent model exist/unique validation,
- remove redundant database queries for Eloquent model manipulations.

Assume we have a database storing some items grouped by categories. While creating an HTTP request handler for the new
item saving, we need to check if given category record exists and associate it the new item. There is a well-known
recommendation to use relation methods like `associate()` to handle relation instantiation at object level. However,
if we follow it along with standard validation, our program performs redundant SQL query. For example:

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

To solve this issue you can use `Illuminatech\ModelRules\Exists` validation rule. During the validation it "remembers"
the last queried model instance, which can be retrieved using `getModel()` method. For example:

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
                $categoryRule = Exists::new(Category::class), // executes SQL query 'SELECT ... FROM categories WHERE id =...' only once
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

You can use `Illuminatech\ModelRules\Unique` to setup a validation for unique model attribute. For example:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminatech\ModelRules\Unique;

class ItemController extends Controller
{
    public function update(Request $request, Item $item)
    {
        $validatedData = $this->validate($request, [
            'name' => [
                'required',
                'string',
            ],
            'slug' => [
                'required',
                'string',
                Unique::new(Item::class, 'slug') // check 'slug' value is unique throughout the items
                    ->ignore($item), // exclude current record from validation
            ],
        ]);
        
        $item = new Item();
        $item->name = $validatedData['name'];
        $item->slug = $validatedData['slug'];
        $item->save();
        // ...
    }
}
```


### Customize Database Query

You can pass query builder instance directly to model validation rule. This allows you to specify any custom search
conditions or use a relation query. For example:

```php
<?php

namespace App\Http\Controllers;

use App\Models\RefundRequest;
use App\Models\RefundReason;
use Illuminate\Http\Request;
use Illuminatech\ModelRules\Exists;

class RefundRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        
        $validatedData = $this->validate($request, [
            'reason_id' => [
                'required',
                'integer',
                $reasonRule = Exists::new(RefundReason::query()->withoutTrashed()), // custom query condition
            ],
            'order_id' => [
                'required',
                'integer',
                $orderRule = Exists::new($user->orders()), // use a relation, e.g. `Order::query()->where('user_id', $user->id)`
            ],
        ]);
        
        $refundRequest = new RefundRequest();
        $refundRequest->reason()->associate($reasonRule->getModel());
        $refundRequest->order()->associate($orderRule->getModel());
        $refundRequest->save();
        // ...
    }
}
```

> Note: this extension does not put explicit restriction on the query builder object type - it simply expected to match
  database query builder notation. Thus, you may create a custom query builder class, which works with special data storage
  like MongoDB or Redis, and pass its instance as a data source. If its methods signature matches `\Illuminate\Database\Query\Builder` -
  it should work. Although it is not guaranteed.


### Customize error message

You can setup a custom error message for model rules using `withMessage()` method. In case a model instance is available
after validation failure, its attributes can be used as a placeholders in the error message using syntax: `model_{attribute}`.
For example:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminatech\ModelRules\Unique;

class ItemController extends Controller
{
    public function update(Request $request, Item $item)
    {
        $validatedData = $this->validate($request, [
            'name' => [
                'required',
                'string',
            ],
            'slug' => [
                'required',
                'string',
                Unique::new(Item::class, 'slug')
                    ->ignore($item)
                    ->withMessage('This slug is already in use at item ID=:model_id'), // on failure produces error "This slug is already in use at item ID=19"
            ],
        ]);
        // ...
    }
}
```


### Validate multiple models

Assume we have a database, where items and categories are linked as 'many-to-many'. In such case the request for the
item storage will contain a list of category IDs, which should be associated with it. The ordinary request handler for
this may look like following:

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
            'category_ids' => [
                'required',
                'array',
            ],
            'category_ids.*' => [
                'exists:categories,id', // executes SQL query 'SELECT ... FROM categories WHERE id =...' in cycle multiple times!
            ],
        ]);
        
        $item = new Item();
        $item->name = $validatedData['name'];
        
        $item->categories()->sync($validatedData['category_ids']);
        
        $item->save();
        // ...
    }
}
```

You can reduce the numbers of database queries for such a scenario using `Illuminatech\ModelRules\MultiExist` validation
rule. For example:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminatech\ModelRules\MultiExist;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'name' => [
                'required',
                'string',
            ],
            'category_ids' => [
                'required',
                'array',
                $categoryRule = MultiExist::new(Category::class), // executes single SQL query 'SELECT ... FROM categories WHERE id IN (...)'
            ],
        ]);
        
        $item = new Item();
        $item->name = $validatedData['name'];
        
        $item->categories()->sync($categoryRule->getModels());
        
        $item->save();
        // ...
    }
}
```

> Note: as you may have guessed, there is also `Illuminatech\ModelRules\MultiUnique` validation rule, however its
  real-world implications are quite limited.

**Heads up!** Remember, that model validation rules are not cumulative, each rule remembers only the last queried model.
Thus, it will not serve you well during nested array validation, like in case you want to store multiple items as batch
per single HTTP request. It is better to separate validation into multiple steps for such cases. For example:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminatech\ModelRules\Exists;

class ItemController extends Controller
{
    public function storeBatch(Request $request)
    {
        $firstRoundValidated = $this->validate($request, [
            'items' => ['required', 'array'],
            'items.*.name' => ['required', 'string'],
            'items.*.category_id' => ['required', 'integer'],
        ]);
        
        $items = [];
        $categoryRule = Exists::new(Category::class);
        foreach ($firstRoundValidated['items'] as $key => $item) {
            $itemData = [];
            Arr::set($itemData, 'items.' . $key, $item); // ensure error message for the correct nested field
            
            $this->validate($itemData, [
                'items.*.category_id' => $categoryRule, // validate single item at once
            ]);
            
            // create item draft:
            $item = new Item();
            $item->name = $item['name'];
            $item->category()->associatte($categoryRule->getModel());
            $items[] = $item;
        }
        
        foreach ($items as $item) {
            $item->save(); // save item drafts
        }
        // ...
    }
}
```


### Working with Form Requests

You can use model rules with [Form Request Validation](https://laravel.com/docs/validation#form-request-validation). For example:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminatech\ModelRules\Exists;

class ItemStoreRequest extends FormRequest
{
    private $categoryRule;
    
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'category_id' => [
                'required',
                'integer',
                $this->categoryRule = Exists::new(Category::class),
            ],
        ];
    }
    
    public function validatedCategory(): Category
    {
        return $this->categoryRule->getModel();
    }
}

class ItemController extends Controller
{
    public function store(ItemStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $item = new Item();
        $item->name = $validatedData['name'];
        
        $item->category()->associate($request->validatedCategory());
        
        $item->save();
        // ...
    }
}
```
