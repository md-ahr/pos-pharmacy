<?php

namespace App\Support;

class SvgChartBuilder
{
    private const int WIDTH = 600;

    private const int PADDING_TOP = 20;

    private const int USABLE_HEIGHT = 150;

    private const int BASELINE = 170;

    /**
     * @param  list<float|int>  $values
     * @return array{line_path: string, area_path: string, total: float, max: float, y_ticks: list<string>}
     */
    public static function lineChart(array $values, int $tickCount = 4): array
    {
        if ($values === []) {
            $flatY = self::PADDING_TOP + self::USABLE_HEIGHT;
            $path = 'M 0 '.$flatY.' L '.self::WIDTH.' '.$flatY;

            return [
                'line_path' => $path,
                'area_path' => $path.' L '.self::WIDTH.' '.self::BASELINE.' L 0 '.self::BASELINE.' Z',
                'total' => 0.0,
                'max' => 0.0,
                'y_ticks' => self::yTicks(1.0, $tickCount),
            ];
        }

        $max = max($values) ?: 1.0;
        $count = count($values);
        $step = $count > 1 ? self::WIDTH / ($count - 1) : 0;

        $points = [];

        foreach ($values as $index => $value) {
            $x = $count > 1 ? (int) round($index * $step) : (int) (self::WIDTH / 2);
            $normalized = $max > 0 ? ((float) $value / $max) : 0.0;
            $y = (int) round(self::PADDING_TOP + self::USABLE_HEIGHT - ($normalized * self::USABLE_HEIGHT));
            $points[] = ['x' => $x, 'y' => $y];
        }

        $linePath = self::smoothPath($points);
        $lastX = $points[array_key_last($points)]['x'];
        $firstX = $points[0]['x'];
        $areaPath = $linePath.' L '.$lastX.' '.self::BASELINE.' L '.$firstX.' '.self::BASELINE.' Z';

        return [
            'line_path' => $linePath,
            'area_path' => $areaPath,
            'total' => (float) array_sum($values),
            'max' => (float) $max,
            'y_ticks' => self::yTicks((float) $max, $tickCount),
        ];
    }

    /**
     * @return list<string>
     */
    public static function yTicks(float $max, int $tickCount = 4): array
    {
        if ($max <= 0) {
            return array_fill(0, $tickCount + 1, '0');
        }

        $ticks = [];

        for ($index = $tickCount; $index >= 0; $index--) {
            $ticks[] = self::formatTick(($max / $tickCount) * $index);
        }

        return $ticks;
    }

    public static function formatTick(float $value): string
    {
        if ($value <= 0) {
            return '0';
        }

        if ($value >= 1000) {
            return number_format($value / 1000, $value >= 10000 ? 0 : 1).'k';
        }

        if ($value >= 100) {
            return number_format($value, 0);
        }

        if ($value >= 10) {
            return number_format($value, 1);
        }

        return number_format($value, 2);
    }

    /**
     * @param  list<array{label: string, value: float|int}>  $items
     * @return list<array{label: string, value: float|int, pct: int}>
     */
    public static function barHeights(array $items): array
    {
        if ($items === []) {
            return [];
        }

        $max = max(array_map(fn (array $item): float => (float) $item['value'], $items)) ?: 1.0;

        return array_map(fn (array $item): array => [
            'label' => $item['label'],
            'value' => $item['value'],
            'pct' => (int) round((((float) $item['value']) / $max) * 100),
        ], $items);
    }

    /**
     * @param  list<array{x: int, y: int}>  $points
     */
    private static function smoothPath(array $points): string
    {
        if ($points === []) {
            return '';
        }

        if (count($points) === 1) {
            return 'M '.$points[0]['x'].' '.$points[0]['y'];
        }

        $path = 'M '.$points[0]['x'].' '.$points[0]['y'];

        for ($index = 0; $index < count($points) - 1; $index++) {
            $start = $points[$index];
            $end = $points[$index + 1];
            $controlOneX = (int) round($start['x'] + ($end['x'] - $start['x']) / 3);
            $controlOneY = $start['y'];
            $controlTwoX = (int) round($start['x'] + (2 * ($end['x'] - $start['x']) / 3));
            $controlTwoY = $end['y'];
            $path .= " C {$controlOneX} {$controlOneY}, {$controlTwoX} {$controlTwoY}, {$end['x']} {$end['y']}";
        }

        return $path;
    }
}
