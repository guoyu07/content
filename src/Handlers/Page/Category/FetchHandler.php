<?php
/**
 * This file is part of Notadd.
 *
 * @author TwilRoad <269044570@qq.com>
 * @copyright (c) 2017, iBenchu.org
 * @datetime 2017-02-15 14:48
 */
namespace Notadd\Content\Handlers\Page\Category;

use Illuminate\Container\Container;
use Notadd\Content\Models\PageCategory;
use Notadd\Foundation\Passport\Abstracts\DataHandler;

/**
 * Class FetchHandler.
 */
class FetchHandler extends DataHandler
{
    /**
     * FetchHandler constructor.
     *
     * @param \Illuminate\Container\Container     $container
     * @param \Notadd\Content\Models\PageCategory $category
     */
    public function __construct(
        Container $container,
        PageCategory $category
    ) {
        parent::__construct($container);
        $this->errors->push($this->translator->trans('content::category.fetch.fail'));
        $this->messages->push($this->translator->trans('content::category.fetch.success'));
        $this->model = $category;
    }

    /**
     * Data for handler.
     *
     * @return array
     */
    public function data()
    {
        if ($this->request->input('with-children')) {
            $categories = $this->model->newQuery()->orderBy('order_id', 'asc')->get();
            $categories->transform(function (PageCategory $category) {
                $children = $this->model->newQuery()->where('parent_id', $category->getAttribute('id'))->orderBy('order_id', 'asc')->get();
                $children->count() && $category->setAttribute('children', $children);
                return $category;
            });
            return $categories;
        }
        if ($this->hasFilter) {
            return $this->model->get();
        } else {
            return $this->model->structure();
        }
    }
}
