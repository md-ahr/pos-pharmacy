<?php

use App\Support\DisplayDateTime;
use Illuminate\Support\Carbon;

it('formats datetimes with am pm', function (): void {
    $date = Carbon::parse('2026-07-02 12:30:00');

    expect(DisplayDateTime::format($date, 'M j, Y g:i A'))->toBe('Jul 2, 2026 12:30 PM');
});

it('formats times with am pm', function (): void {
    $date = Carbon::parse('2026-07-02 18:45:00');

    expect(DisplayDateTime::format($date, 'g:i A'))->toBe('6:45 PM');
});

it('returns a placeholder for null values', function (): void {
    expect(DisplayDateTime::format(null))->toBe('—');
});
