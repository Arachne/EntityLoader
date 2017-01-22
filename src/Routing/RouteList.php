<?php

/*
 * This file is part of the Arachne package.
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Routing;

use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Nette\Application\Request;
use Nette\Application\Routers\RouteList as BaseRouteList;
use Nette\Http\Url;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 *
 * @deprecated use {@link Arachne\EntityLoader\Routing\RouterWrapper} instead
 */
class RouteList extends BaseRouteList
{
    /**
     * @var RequestEntityUnloader
     */
    private $unloader;

    /**
     * @param RequestEntityUnloader $unloader
     * @param string                $module
     */
    public function __construct(RequestEntityUnloader $unloader, $module = null)
    {
        parent::__construct($module);
        $this->unloader = $unloader;
    }

    /**
     * {@inheritdoc}
     */
    public function constructUrl(Request $request, Url $refUrl)
    {
        $request = clone $request;
        $this->unloader->filterOut($request, true);

        return parent::constructUrl($request, $refUrl);
    }
}
