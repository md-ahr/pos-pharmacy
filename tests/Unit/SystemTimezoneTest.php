<?php

use App\Support\SystemTimezone;

it('returns a configured timezone when APP_TIMEZONE is set', function (): void {
    $_ENV['APP_TIMEZONE'] = 'Europe/London';
    $_SERVER['APP_TIMEZONE'] = 'Europe/London';
    putenv('APP_TIMEZONE=Europe/London');

    expect(SystemTimezone::detect())->toBe('Europe/London');
});

it('returns a valid timezone identifier', function (): void {
    expect(SystemTimezone::detect())->toBeIn(timezone_identifiers_list());
});

it('detects the server timezone from the localtime symlink when available', function (): void {
    unset($_ENV['APP_TIMEZONE'], $_SERVER['APP_TIMEZONE']);
    putenv('APP_TIMEZONE');
    putenv('TZ');

    $link = @readlink('/etc/localtime');

    if ($link === false || ! preg_match('#(?:zoneinfo/)([A-Za-z_]+(?:/[A-Za-z_]+)+)$#', $link, $matches)) {
        expect(true)->toBeTrue();

        return;
    }

    expect(SystemTimezone::detect())->toBe($matches[1]);
});
