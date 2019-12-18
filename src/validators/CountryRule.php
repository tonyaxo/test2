<?php declare(strict_types=1);

namespace Bogatyrev\validators;

class CountryRule extends BaseRule
{
    protected function getDataUri(): string
    {
        return 'countries';
    }
}
