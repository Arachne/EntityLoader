<?php

declare(strict_types=1);

namespace Arachne\EntityLoader\Routing;

use Arachne\EntityLoader\Application\Envelope;
use Closure;
use Nette\Application\Routers\Route as BaseRoute;
use Nette\InvalidArgumentException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class Route extends BaseRoute
{
    /**
     * @param string $mask
     * @param mixed  $metadata
     * @param int    $flags
     */
    public function __construct($mask, $metadata = [], $flags = 0)
    {
        // Copied from Nette\Application\Routers\Route::__construct().
        if (is_string($metadata)) {
            $pos = strrpos($metadata, ':');
            if ($pos === false) {
                throw new InvalidArgumentException(sprintf('Second argument must be array or string in format Presenter:action, %s given.', $metadata));
            }
            $metadata = [
                self::PRESENTER_KEY => substr($metadata, 0, $pos),
                'action' => $pos === strlen($metadata) - 1 ? null : substr($metadata, $pos + 1),
            ];
        } elseif ($metadata instanceof Closure) {
            $metadata = [
                self::PRESENTER_KEY => 'Nette:Micro',
                'callback' => $metadata,
            ];
        }

        // Filter for handling Envelopes correctly.
        $filter = function (array $parameters) use ($metadata) {
            foreach ($parameters as $key => &$value) {
                if ($value instanceof Envelope && !isset($metadata[$key][self::FILTER_OUT])) {
                    $value = $value->getIdentifier();
                }
            }

            return $parameters;
        };

        // Hack to invoke the filter after original global filter.
        if (isset($metadata[null][self::FILTER_OUT])) {
            $original = $metadata[null][self::FILTER_OUT];
            $metadata[null][self::FILTER_OUT] = function (array $parameters) use ($original, $filter) {
                $parameters = $original($parameters);
                if (!is_array($parameters)) {
                    return $parameters;
                }

                return $filter($parameters);
            };
        } else {
            $metadata[null][self::FILTER_OUT] = $filter;
        }

        parent::__construct($mask, $metadata, $flags);
    }
}
