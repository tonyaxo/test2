<?php declare(strict_types=1);

namespace Bogatyrev\validators;

class TimezoneRule extends BaseRule
{
    protected function getDataUri(): string
    {
        return 'timezones';
    }
}
