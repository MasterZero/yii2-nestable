<?php

namespace masterzero\widgets;

use \Yii;
use kartik\icons\FontAwesomeAsset;
use yii\bootstrap\ButtonGroup;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

use kartik\icons\Icon;
use yii\helpers\Url;

class Nestable extends \slatiusa\nestable\Nestable
{

    public $modelOptions = null;

    public $columns = ['name' => 'name'];
    public $buttons = null;
    public $driveController = '';
    public $pluginEvents = null;

    public function init()
    {


        if(!is_null($this->query)){
            $auto = $this->query->roots();
            $this->query = $auto;
        }

        if(is_null($this->modelOptions)){
            $this->modelOptions = ['name' => function($data){return $this->prepareRow($data);}];
        }


        if(is_null($this->pluginEvents)){
            $this->pluginEvents = [ 'change' =>
                    'function(e)
                    {
                        data = $(this).nestable("serialize");
                        $.ajax({
                            type: "GET",
                            url: "'.Url::to(['restruct']).'",
                            data: "data="+JSON.stringify(data),
                            success: function(data) {
                                if(!data)
                                    console.error( "unable to restuct this sets" );
                                },
                            error: function(data) {
                                console.error( "unable to restuct this sets" );
                            },
                            contentType : \'application/json\',
                        });
                    }',
                ];
        }



         $this->columns['url'] = function($data){
                return Url::toRoute([$this->driveController.'update', 'id' => $data->primaryKey]);
            };

        if(is_null($this->buttons)){
            $model = new $this->query->modelClass;
            $this->buttons = [

                ['label' => Icon::show('pencil', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'update', 'id'=>$data->primaryKey]);},
                    'options'=>['title'=>'Редактировать', 'data-pjax' => 0]],

                ['label' => Icon::show('eye', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'hide', 'id'=>$data->primaryKey]);},
                    'options'=>[
                        'title'=>'Скрыть',
                        'data-method' => 'POST',
                        'data-pjax' => '0',
                        'data-confirm'=>"Вы действительно хотите скрыть этот элемент?",
                    ],
                    'visible' => function($data){ return $data->hasAttribute('removed')&&!$data->removed;}],
                ['label' => Icon::show('eye-slash', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'hide', 'id'=>$data->primaryKey]);},
                    'options'=>['title'=>'Восстановить'],
                    'visible' => function($data){ return $data->hasAttribute('removed')&&$data->removed;}],
                ['label' => Icon::show('remove', [], Icon::FA),
                    'url' => function($data){ return Url::toRoute([$this->driveController.'delete', 'id'=>$data->primaryKey]);},
                    'options'=>[
                        'title'=>'Удалить',
                        'data-method' => 'POST',
                        'data-pjax' => '0',
                        'data-confirm'=>"Вы действительно хотите удалить этот элемент?",
                    ],
                    ],
            ];
        }

        $this->options['class'] = 'nestable'.(isset($this->options['class'])?' '.$this->options['class']:'');

        parent::init();
    }


    /**
     * Register client assets
     */
    public function registerAssets() {
        $view = $this->getView();
        FontAwesomeAsset::register($view);
        NestableAsset::register($view);
        parent::registerAssets();
    }

    private function prepareRow($data){

        $row = '';

        $name = ArrayHelper::getValue($this->columns, 'name', 'name');
        $content = (is_callable($name) ? call_user_func($name, $data) : $data->{$name});

        if(count($this->columns)<2){
            $row = $content;
        }else{
            $name = ArrayHelper::getValue($this->columns, 'url');
            if(is_callable($name)){
                $row = Html::a($content, call_user_func($name, $data), ['data-pjax'=>0]);
            }else{
                $row = Html::a($content,
                    $data->hasAttribute($name)?
                        $data->{$name}:
                        $name);
            }
        }

        if(!is_null($this->buttons)){
            $template = '<div class="pull-right" style="margin-top: -2px;">{buttons}</div>';
            $myButtons = $this->buttons;
            foreach($myButtons as $key => &$button){
                if(is_string($button))
                    continue;

                if(array_key_exists('visible', $button)){
                    $name = ArrayHelper::getValue($button, 'visible');
                    if(is_callable($name)){
                        $button['visible'] = call_user_func($name, $data);
                    }
                    if(!$button['visible']&&!is_null($key)){
                        unset($myButtons[$key]);
                        continue;
                    }
                }
                $label = $button['label'];
                $url = ArrayHelper::getValue($button, 'url', '#');
                unset($button['label']);
                if(isset($button['url']))
                    if(is_callable($url))
                        $url = call_user_func($url, $data);

                $options = $button['options'];
                $options['class'] = 'btn btn-default'.(isset($options['class'])?' '.$options['class']:'');

                $button = Html::a($label, $url, $options);
            }
            $row .= strtr($template, ['{buttons}' =>
                ButtonGroup::widget([
                    'encodeLabels'  => false,
                    'options' => ['class' => 'btn-group-xs'],
                    'buttons' => $myButtons])]);
        }

        return $row;
    }
}
