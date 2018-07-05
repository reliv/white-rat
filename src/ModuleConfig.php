<?php

namespace Reliv\WhiteRat;

class ModuleConfig
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'config_factories' => [
                    FilterInterface::class => [
                        'class' => Filter::class,
                        'arguments' => []
                    ]
                ]
            ]
        ];
    }
}
