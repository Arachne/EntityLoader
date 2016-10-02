<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\EntityLoader\Application;

use Arachne\EntityLoader\EntityUnloader;
use Nextras\Application\UI\SecuredLinksPresenterTrait as BaseSecuredLinksPresenterTrait;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
trait SecuredLinksPresenterTrait
{
    use BaseSecuredLinksPresenterTrait {
        getCsrfToken as private parentGetCsrfToken;
    }

    /**
     * @var EntityUnloader
     */
    private $entityUnloader;

    public function injectEntityUnloader(EntityUnloader $entityUnloader)
    {
        $this->entityUnloader = $entityUnloader;
    }

    /**
     * @param string $control
     * @param string $method
     * @param array  $params
     *
     * @return string
     */
    public function getCsrfToken($control, $method, $params)
    {
        array_walk(
            $params,
            function (&$value) {
                if (is_object($value)) {
                    $value = $this->entityUnloader->filterOut($value);
                }
            }
        );

        return $this->parentGetCsrfToken($control, $method, $params);
    }
}
