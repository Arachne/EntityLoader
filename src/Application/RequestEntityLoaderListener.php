<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Kdyby\Events\Subscriber;
use Nette\Application\Request;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RequestEntityLoaderListener extends Object implements Subscriber
{

	/** @var RequestEntityLoader */
	private $loader;

	/**
	 * @param RequestEntityLoader $loader
	 */
	public function __construct(RequestEntityLoader $loader)
	{
		$this->loader = $loader;
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return [
			'Nette\Application\Application::onRequest' => 'requestHandler',
		];
	}

	/**
	 * @param Request $request
	 */
	public function requestHandler(Request $request)
	{
		$this->loader->filterIn($request);
	}

}
