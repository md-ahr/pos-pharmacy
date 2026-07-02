<?php

namespace App\Support;

class SystemTimezone
{
    public static function detect(): string
    {
        $configured = self::readEnvironmentValue('APP_TIMEZONE');

        if ($configured !== null && self::isValid($configured)) {
            return $configured;
        }

        $environmentTimezone = self::readEnvironmentValue('TZ');

        if ($environmentTimezone !== null && self::isValid($environmentTimezone)) {
            return $environmentTimezone;
        }

        $fromLocaltime = self::fromLocaltimeSymlink();

        if ($fromLocaltime !== null) {
            return $fromLocaltime;
        }

        $phpDefault = date_default_timezone_get();

        if ($phpDefault !== '' && self::isValid($phpDefault) && $phpDefault !== 'UTC') {
            return $phpDefault;
        }

        return 'UTC';
    }

    private static function fromLocaltimeSymlink(): ?string
    {
        $link = @readlink('/etc/localtime');

        if ($link === false) {
            return null;
        }

        if (! preg_match('#(?:zoneinfo/)([A-Za-z_]+(?:/[A-Za-z_]+)+)$#', $link, $matches)) {
            return null;
        }

        $timezone = $matches[1];

        return self::isValid($timezone) ? $timezone : null;
    }

    private static function isValid(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }

    private static function readEnvironmentValue(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }
}
