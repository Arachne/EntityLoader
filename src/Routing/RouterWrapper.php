<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Routing;

use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Nette\Application\Request;
use Nette\Application\IRouter;
use Nette\Http\IRequest;
use Nette\Http\Url;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouterWrapper implements IRouter
{
    /** @var IRouter */
    private $router;

    /** @var RequestEntityUnloader */
    private $unloader;

    /** @var bool */
    private $envelopes;

    /**
     * @param IRouter $router
     * @param RequestEntityUnloader $unloader
     * @param bool $envelopes
     */
    public function __construct(IRouter $router, RequestEntityUnloader $unloader, $envelopes = false)
    {
        $this->router = $router;
        $this->unloader = $unloader;
        $this->envelopes = $envelopes;
    }

    /**
     * Maps HTTP request to a Request object.
     * @return Request|null
     */
    public function match(IRequest $httpRequest)
    {
        return $this->router->match($httpRequest);
    }

    /**
     * Constructs absolute URL from Request object.
     * @param Request $request
     * @param Url $refUrl
     * @return string|null
     */
    public function constructUrl(Request $request, Url $refUrl)
    {
        $request = clone $request;
        $this->unloader->filterOut($request, $this->envelopes);
        return $this->router->constructUrl($request, $refUrl);
    }
}
