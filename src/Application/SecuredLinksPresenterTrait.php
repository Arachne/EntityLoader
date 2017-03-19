<?php

declare(strict_types=1);

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
