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
        getCsrfToken as private nextrasGetCsrfToken;
    }

    /**
     * @var EntityUnloader
     */
    private $entityUnloader;

    public function injectEntityUnloader(EntityUnloader $entityUnloader): void
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
    public function getCsrfToken($control, $method, $params): string
    {
        array_walk(
            $params,
            function (&$value) {
                if (is_object($value)) {
                    $value = $this->entityUnloader->filterOut($value);
                }
            }
        );

        return $this->nextrasGetCsrfToken($control, $method, $params);
    }
}
