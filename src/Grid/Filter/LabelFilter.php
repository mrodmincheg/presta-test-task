<?php

declare(strict_types=1);

namespace PrestaShop\Module\ProductLabel\Grid\Filter;

use PrestaShop\Module\ProductLabel\Grid\Definition\Factory\ProductLabelGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

class LabelFilter extends Filters
{
    protected $filterId = ProductLabelGridDefinitionFactory::GRID_ID;

    public static function getDefaults()
    {
        return [
            'limit' => 10,
            'offset' => 0,
            'orderBy' => 'id',
            'sortOrder' => 'asc',
            'filters' => [],
        ];
    }
}
