**Example**

## Family Trees

In this example, we will be demonstrating a database of people and their relationships with each other.

We will be using <a href="https://github.com/laravel/laravel">Laravel</a> for this example.

---

### Getting Started
A family tree, or pedigree chart, is a chart representing family relationships in a conventional tree structure. This demonstration will focus on how to achieve storing and retrieving the relationships using the package.

#### **Migrations**
We need to generate two migration files: the `families` table where we store all family members; and
the `familytree` table where we store the relationship of family members with each other.

Create the `families` migration:
```bash
php artisan make:migration create_families_table
```

For the `families` table, the simpliest required column fields may look something like the following:
```php
// <timestamp>_create_families_table.php

Schema::create('families', function (Blueprint $table) {
    $table->id();
    $table->string('prefixname')->nullable();
    $table->string('firstname');
    $table->string('middlename')->nullable();
    $table->string('lastname');
    $table->string('suffixname')->nullable();
    $table->string('house_id')->nullable();
    $table->timestamps();
    $table->foreign('house_id')
          ->references('id')->on('houses')
          ->onDelete('cascade')
          ->onUpdate('cascade');
});
```
_Note: the column `house_id` is not required for this demonstration, and is only added for fun._

Next, create the closurable `familytree` migration:
```bash
php artisan make:closurable family
```
```php
// <timestamp>_create_familytree_table.php

Schema::create('familytree', function (Blueprint $table) {
    $table->unsignedBigInteger('ancestor_id')->index();
    $table->unsignedBigInteger('descendant_id')->index();
    $table->unsignedBigInteger('depth')->index()->default(0);
    $table->unsignedBigInteger('root')->index()->default(0);
    $table->unique(['ancestor_id', 'descendant_id']);
    $table->index(['ancestor_id', 'descendant_id', 'depth']);
    $table->index(['descendant_id', 'depth']);
    $table->index(['depth', 'root']);
    $table->foreign('ancestor_id')
          ->references('id')
          ->on('family')
          ->onDelete('cascade')
          ->onUpdate('cascade');
    $table->foreign('descendant_id')
          ->references('id')
          ->on('family')
          ->onDelete('cascade')
          ->onUpdate('cascade');
});
```
_You may, of course, edit the closurable migration file to suit your requirements._

#### **Model**
If you haven't already, generate the model for the `families` migration file.
```bash
php artisan make:model Family
```

#### **Using the Closurable Trait or Abstract Class**
There are two ways to use the `Codrasil\Closurable` package:

* via Trait
```php
use Codrasil\Closurable\Closurable;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use Closurable;
}
```
* via Abstract class
```php
use Codrasil\Closurable\Model as Closurable;

class Family extends Closurable
{

}
```

If you passed in a different table name for the closure table, you may override the value using the `$closureTable` property:
```php
use Codrasil\Closurable\Model as Closurable;

class Family extends Closurable
{
    /**
     * The closure table associated with the model.
     *
     * @var string
     */
    protected $closureTable = 'some_other_table_tree';
}
```

---

### Operations

In addition with the instructions above, this section will assume you also have set up the controllers, routes, views, and factories related to the Family resource.

More importantly, for this demonstration, you must have a `FamilyFactory` already generated to make it easier to follow this document.

##### **Sample Data**
Assume we have the following data already stored in the `families` table:
```mysql
> SELECT id, firstname, lastname, house_id FROM families WHERE house_id = 1;

    +----+------------------+-----------------+----------+
    | id | firstname        | lastname        | house_id |
    +----+---------------------------------------+-------+
    |  1 | Eddard           | Stark           | 1        |
    |  2 | Benjen           | Stark           | 1        |
    |  3 | Lyanna           | Stark           | 1        |
    |  4 | Rickard          | Stark           | 1        |
    |  5 | Rob              | Stark           | 1        |
    |  6 | Sansa            | Stark           | 1        |
    |  7 | Arya             | Stark           | 1        |
    |  8 | Bran             | Stark           | 1        |
    |  9 | Rickon           | Stark           | 1        |
    | 10 | Jon              | Snow            | 1        |
    +----+------------------+-----------------+----------+
```

---

#### Saving Root Family
From the stored data, we know that the Stark's family tree starts with Rickard (id:4).
To save this 'root', we need to attach `id:4` to self:
```php
$rickard = Family::find(4);
$rickard->attachToSelf();
```

On the `familytree` table:
```
> SELECT ancestor_id, descendant_id, depth, root FROM familytree;

    +-------------+---------------+-------+------+
    | ancestor_id | descendant_id | depth | root |
    +-------------+---------------+-------+------+
    |           4 |             4 |     0 |    1 |
    +-------------+---------------+-------+------+
```

---

#### Adding Children
Let us associate Rickard's (user:4) children to him.
```php
$rickard = Family::find(4);

$ned = Family::find(1);
$rickard->addChild($ned);
```

The `familytree` table should now have the following data:
```
> SELECT ancestor_id, descendant_id, depth, root FROM familytree;

    +-------------+---------------+-------+------+
    | ancestor_id | descendant_id | depth | root |
    +-------------+---------------+-------+------+
    |           1 |             1 |     0 |    0 |
    |           4 |             4 |     0 |    1 |
    |           4 |             1 |     1 |    0 |
    +-------------+---------------+-------+------+
```

Add the other children:
```php
$rickard = Family::find(4);

$ben = Family::find(2);
$lyanna = Family::find(3);

$rickard->addChildren([$ben, $lyanna]);
```

The `familytree` table should now have the following data:
```
> SELECT ancestor_id, descendant_id, depth, root FROM familytree;

    +-------------+---------------+-------+------+
    | ancestor_id | descendant_id | depth | root |
    +-------------+---------------+-------+------+
    |           1 |             1 |     0 |    0 |
    |           2 |             2 |     0 |    0 |
    |           3 |             3 |     0 |    0 |
    |           4 |             4 |     0 |    1 |
    |           4 |             1 |     1 |    0 |
    |           4 |             2 |     1 |    0 |
    |           4 |             3 |     1 |    0 |
    +-------------+---------------+-------+------+
```

**Visual Representation:**
```
Rickard
  ↳ Eddard
  ↳ Benjen
  ↳ Lyanna
```

Next, let's add Ned's children to the family tree:
```php
$ned = Family::find(1);

$children = [
  $rob = Family::find(5),
  $sansa = Family::find(6),
  $arya = Family::find(7),
  $bran = Family::find(8),
  $rickon = Family::find(9),
  $jon = Family::find(10),
];

$ned->addChildren($children);
```

If you query the `familytree` again, it should reflect the additions made.

_(Ommitted SQL query section for brevity.)_

**Visual Representation:**
```
Rickard
  ↳ Eddard
      ↳ Rob
      ↳ Sansa
      ↳ Arya
      ↳ Bran
      ↳ Rickon
      ↳ Jon
  ↳ Benjen
  ↳ Lyanna
```

#### Updating Parents
This section demonstrates the update operations on the closure table.

Using our existing data, we will update Jon's parent (and spoilers for ASOIAF ahead).
```php
$jon = Family::find(10);

$lyanna = Family::find(3);

$jon->updateParent($lyanna);
```

**Visual Representation:**
```
Rickard
  ↳ Eddard
      ↳ Rob
      ↳ Sansa
      ↳ Arya
      ↳ Bran
      ↳ Rickon
  ↳ Benjen
  ↳ Lyanna
      ↳ Jon
```

---

#### Displaying Children

To access a parent's children, use the children() method:

```php
$ned = Family::find(1);

...

if ($ned->hasChildren()) {
  foreach ($ned->children() $as $child) {
      echo $child->firstname . PHP_EOL;
  }
}
```

Result:
```
Rob
Sansa
Arya
Bran
Rickon
```

---

#### Conclusion

In this document, we have created a family tree originating from a single node (root ancestor). We have also added children to parents. And lastly, we have updated a child's parent.

If you are developing a system like what's demonstrated in this document, you will probably also have to store the parent's spouse. In this case, you can add another table called `spouses` with columns `spouse_id` and `family_id`.

Learn more of the applications of this package by visiting the <a href="../examples">docs</a> folder.
