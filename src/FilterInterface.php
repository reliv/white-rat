<?php

namespace Reliv\WhiteRat;

interface FilterInterface
{
    public function validate(array $rules) : FilterInterface;
    public function __invoke(array $subject, array $rules) : array;
}
