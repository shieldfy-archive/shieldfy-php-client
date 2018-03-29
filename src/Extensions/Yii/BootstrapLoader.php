<?php
namespace Shieldfy\Extensions\Yii;

use yii\base\BootstrapInterface;
use yii\base\Application;
/**
 * https://www.yiiframework.com/doc/guide/2.0/en/structure-extensions#creating-extensions
 */
class BootstrapClass implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
            echo 'Hello Yii';
        });
    }
}
