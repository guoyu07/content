<?php
/**
 * This file is part of Notadd.
 *
 * @author TwilRoad <heshudong@ibenchu.com>
 * @copyright (c) 2016, notadd.com
 * @datetime 2016-10-08 17:12
 */
namespace Notadd\Content;

use Notadd\Content\Managers\ArticleManager;
use Notadd\Content\Managers\CategoryManager;
use Notadd\Content\Managers\PageManager;
use Notadd\Foundation\Module\Abstracts\Module;

/**
 * Class Module.
 */
class ModuleServiceProvider extends Module
{
    /**
     * Boot service provider.
     */
    public function boot()
    {
        $this->loadTranslationsFrom(realpath(__DIR__ . '/../resources/translations'), 'content');
        $this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'content');
    }

    /**
     * Register services.
     */
    public function register()
    {
        $this->app->alias('article.manager', ArticleManager::class);
        $this->app->alias('category.manager', CategoryManager::class);
        $this->app->alias('page.manager', PageManager::class);
        $this->app->singleton('article.manager', function ($app) {
            $manager = new ArticleManager($app, $app['events']);

            return $manager;
        });
        $this->app->singleton('category.manager', function ($app) {
            $manager = new CategoryManager($app, $app['events']);

            return $manager;
        });
        $this->app->singleton('page.manager', function ($app) {
            $manager = new PageManager($app, $app['events']);

            return $manager;
        });
    }
}
