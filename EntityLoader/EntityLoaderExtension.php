<?php

/**
 * This file is part of the EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and licence information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader;

/**
 * @author Jáchym Toušek
 */
class EntityLoaderExtension extends \Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('loader'))
			->setClass('Arachne\EntityLoader\EntityLoader');
	}

}
