<?php

namespace Reliv\WhiteRat;

interface WhitelistInterface
{
    public function __invoke(array $subject) : array;
}
