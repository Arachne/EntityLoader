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
use Arachne\EntityLoader\Application\RequestEntityUnloader;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\Utils\Strings;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
trait EntityLoaderPresenterTrait
{

	/** @var RequestEntityLoader */
	private $loader;

	/** @var RequestEntityUnloader */
	private $unloader;

	/**
	 * @param RequestEntityLoader $loader
	 * @param RequestEntityUnloader $unloader
	 */
	final public function injectEntityLoader(RequestEntityLoader $loader, RequestEntityUnloader $unloader)
	{
		$this->loader = $loader;
		$this->unloader = $unloader;
	}

	/**
	 * Stores request to session.
	 * @param Request $request
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest($request = null, $expiration = '+ 10 minutes')
	{
		// both parameters are optional
		if ($request === null) {
			$request = $this->request;
		} elseif (!$request instanceof Request) {
			$expiration = $request;
			$request = $this->request;
		}

		$request = clone $request;
		$this->unloader->filterOut($request);

		$session = $this->getSession('Arachne.Application/requests');
		do {
			$key = Strings::random(5);
		} while (isset($session[$key]));

		$session[$key] = [ $this->getUser()->getId(), $request ];
		$session->setExpiration($expiration, $key);
		return $key;
	}

	/**
	 * Restores current request to session.
	 * @param string $key
	 */
	public function restoreRequest($key)
	{
		$session = $this->getSession('Arachne.Application/requests');
		if (!isset($session[$key]) || ($session[$key][0] !== null && $session[$key][0] !== $this->getUser()->getId())) {
			return;
		}
		$request = clone $session[$key][1];
		unset($session[$key]);

		try {
			$this->loader->filterIn($request);
		} catch (BadRequestException $e) {
			return;
		}
		$request->setFlag(Request::RESTORED, true);
		$parameters = $request->getParameters();
		$parameters[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
		$request->setParameters($parameters);
		$this->sendResponse(new ForwardResponse($request));
	}

}
