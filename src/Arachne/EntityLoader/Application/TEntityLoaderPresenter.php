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
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\InvalidStateException;
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
	 * Stores current request to session.
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		$session = $this->getSession('Arachne.Application/requests');
		do {
			$key = Strings::random(5);
		} while (isset($session[$key]));
		$request = clone $this->request;
		if (!$this->loader->removeEntities($request)) {
			throw new InvalidStateException('Failed to remove entities from request.');
		}
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
		if (!$this->loader->loadEntities($request)) {
			return;
		}
		$request->setFlag(Request::RESTORED, TRUE);
		$parameters = $request->getParameters();
		$parameters[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
		$request->setParameters($parameters);
		$this->sendResponse(new ForwardResponse($request));
	}

}
