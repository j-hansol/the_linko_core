<?php

namespace App\OpenApi;

use OpenApi\Annotations\Parameter;

/**
 * @@Annotation
 */
class QueryParameter extends Parameter {
    function __construct(array $properties) {
        $properties['in'] = 'query';
        parent::__construct($properties);
    }
}
