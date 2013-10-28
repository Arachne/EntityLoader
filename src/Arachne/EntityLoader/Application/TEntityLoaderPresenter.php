<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\EntityLoader;
use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\Utils\Strings;

/**
 * @author Jáchym Toušek
 */
trait TEntityLoaderPresenter
{

	/** @var EntityLoader */
	private $loader;

	final public function injectEntityLoader(EntityLoader $loader)
	{
		$this->loader = $loader;
	}

	/**
	 * Stores request to session.
	 * @param Request $request
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest($request = NULL, $expiration = '+ 10 minutes')
	{
		if ($request === NULL) {
			$request = $this->request;
		} elseif (!$request instanceof Request) { // first parameter is optional
			$expiration = $request;
			$request = $this->request;
		}

		$request = clone $request;
		$this->loader->removeEntities($request);

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
		if (!isset($session[$key]) || ($session[$key][0] !== NULL && $session[$key][0] !== $this->getUser()->getId())) {
			return;
		}
		$request = $session[$key][1];
		unset($session[$key]);

		try {
			$this->loader->loadEntities($request);
		} catch (BadRequestException $e) {
			return;
		}
		$request->setFlag(Request::RESTORED, TRUE);
		$parameters = $request->getParameters();
		$parameters[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
		$request->setParameters($parameters);
		$this->sendResponse(new ForwardResponse($request));
	}

}
