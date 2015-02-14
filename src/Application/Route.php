<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Nette\Application\Routers\Route as BaseRoute;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Route extends BaseRoute
{

	/**
	 * {@inheritdoc}
	 */
	public function __construct($mask, $metadata = [], $flags = 0)
	{
		$filter = function (array $parameters) use ($metadata) {
			foreach ($parameters as $key => & $value) {
				if ($value instanceof Envelope && !isset($metadata[$key][self::FILTER_OUT])) {
					$value = $value->getIdentifier();
				}
			}
			return $parameters;
		};

		if (isset($metadata[NULL][self::FILTER_OUT])) {
			$custom = $metadata[NULL][self::FILTER_OUT];
			$metadata[NULL][self::FILTER_OUT] = function (array $parameters) use ($custom, $filter) {
				$parameters = call_user_func($custom, $parameters);
				if (!is_array($parameters)) {
					return $parameters;
				}
				return call_user_func($filter, $parameters);
			};
		} else {
			$metadata[NULL][self::FILTER_OUT] = $filter;
		}

		parent::__construct($mask, $metadata, $flags);
	}

}
