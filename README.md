# yii2-nestable
Plugin for [yii2-nestable by ASlatius](https://github.com/ASlatius/yii2-nestable) to view nested sets nodes

Installation
------------

Before install you need have [composer](http://getcomposer.org/download/).


Make sure you've attached the [NestedSetsBehavior by creocoder](https://github.com/creocoder/yii2-nested-sets) correctly to your model.
Then add the node move handler to you controller by attaching the supplied action;

Either run

```
$ php composer.phar require masterzero/yii2-nestable "*"
```

or add

```
"masterzero/yii2-nestable": "*"
```

to the ```require``` section of your `composer.json` file.

## Usage


```
<?= masterzero\widgets\Nestable::widget([
            'query' => \common\models\NestedSetModel::find(),
        ]);
?>
```
