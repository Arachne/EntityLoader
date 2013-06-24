<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek
 */
interface IParameterFinder
{

	/**
	 * @param \Nette\Application\Request $request
	 * @return array
	 */
	public function getEntityParameters(\Nette\Application\Request $request);

}
