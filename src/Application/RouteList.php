<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\Application\RequestEntityLoader;
use Arachne\EntityLoader\Exception\UnexpectedValueException;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Routers\RouteList as BaseRouteList;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Url;

/**
 * @author Jáchym Toušek
 */
class RouteList extends BaseRouteList
{

	/** @var RequestEntityLoader */
	private $loader;

	/**
	 * @param EntityLoader $loader
	 * @param string $module
	 */
	public function __construct(RequestEntityLoader $loader, $module = NULL)
	{
		parent::__construct($module);
		$this->loader = $loader;
	}

	/**
	 * Maps HTTP request to a Request object.
	 * @param IRequest $httpRequest
	 * @return Request|NULL
	 * @throws BadRequestException
	 */
	public function match(IRequest $httpRequest)
	{
		$request = parent::match($httpRequest);
		if ($request instanceof Request) {
			try {
				$this->loader->filterIn($request);
			} catch (UnexpectedValueException $e) {
				throw new BadRequestException('Request has invalid parameter.', IResponse::S404_NOT_FOUND, $e);
			}
		}
		return $request;
	}

	/**
	 * Constructs absolute URL from Request object.
	 * @param Request $request
	 * @param Url $refUrl
	 * @return string|NULL
	 */
	public function constructUrl(Request $request, Url $refUrl)
	{
		$request = clone $request;
		$this->loader->filterOut($request, TRUE);
		return parent::constructUrl($request, $refUrl);
	}

}
