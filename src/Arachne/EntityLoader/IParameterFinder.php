<?php

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
