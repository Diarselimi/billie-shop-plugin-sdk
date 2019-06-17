<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;

class AugmentMainInfo implements ProcessorInterface
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __invoke(Analysis $analysis)
    {
        /** @var OA\Info $annotation */
        foreach ($analysis->getAnnotationsOfType(OA\Info::class) as $annotation) {
            if (!is_array($annotation->x)) {
                $annotation->x = [];
            }
            $annotation->title = $this->data['title'];
            $annotation->version = $this->data['version'];
            $annotation->x['logo'] = ['url' => $this->data['logo'], 'href' => '#'];
            $annotation->description = $this->data['info_description'];
        }
    }
}
