<?php
/**
 * This file is part of Notadd.
 *
 * @author        TwilRoad <heshudong@ibenchu.com>
 * @copyright (c) 2017, notadd.com
 * @datetime      2017-11-08 11:54
 */
namespace Notadd\Content\GrqphQL\Mutations;

use Notadd\Foundation\GraphQL\Abstracts\Mutation;

/**
 * Class DeleteArticleCategoryMutation.
 */
class DeleteArticleCategoryMutation extends Mutation
{
    /**
     * @return array
     */
    public function args(): array
    {
        return [];
    }

    /**
     * @param $root
     * @param $args
     *
     * @return mixed
     */
    public function resolve($root, $args)
    {
        // TODO: Implement resolve() method.
    }
}
