<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { margin-top: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .summary { margin-top: 12px; }
        .summary span { display: inline-block; margin-right: 16px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $period }}</p>

    @if(!empty($summary))
        <div class="summary">
            @foreach($summary as $key => $value)
                @if(!is_array($value))
                    <span><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</span>
                @endif
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">No data for this report.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
