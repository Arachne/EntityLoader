<?php

/**
 * This file is part of the Arachne EntityLoader extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\DI;

/**
 * @author Jáchym Toušek
 */
class EntityLoaderExtension extends \Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('parameterFinder'))
				->setClass('Arachne\EntityLoader\ParameterFinder')
				->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('loader'))
				->setClass('Arachne\EntityLoader\EntityLoader', array(
					$this->prefix('@parameterFinder'),
				));
	}

}
