<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Streams admin submission exports. Rows come from Submission::allWithExpo(),
 * already scoped to the requested expo (or all expos) by the caller.
 */
class Exporter
{
    private const HEADERS = [
        'Submitted At', 'Expo', 'Full Name', 'Phone', 'Project Location',
        'Interests', 'Other Interest Detail', 'Follow-up Method', 'Email',
        'Message', 'Possible Duplicate',
    ];

    public static function streamCsv(array $submissions, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, self::HEADERS, ',', '"', '\\');

        foreach (self::rowsFor($submissions) as $row) {
            fputcsv($out, $row, ',', '"', '\\');
        }

        fclose($out);
    }

    public static function streamXlsx(array $submissions, string $filename): void
    {
        $rows = self::rowsFor($submissions);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        // Phone (column D) must survive as literal text — otherwise Excel's
        // auto-numeric detection eats the leading "+" on international numbers.
        foreach (array_values($rows) as $i => $row) {
            $sheet->setCellValueExplicit('D' . ($i + 2), $row[3], DataType::TYPE_STRING);
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
    }

    private static function rowsFor(array $submissions): array
    {
        $rows = [];

        foreach ($submissions as $submission) {
            $interests = Submission::interestsFor((int) $submission['id']);
            $names = array_map(static fn (array $i): string => $i['name'], $interests);

            $otherText = '';
            foreach ($interests as $interest) {
                if (!empty($interest['other_text'])) {
                    $otherText = $interest['other_text'];
                }
            }

            $rows[] = [
                date('Y-m-d H:i', strtotime((string) $submission['submitted_at'])),
                $submission['expo_name'],
                $submission['full_name'],
                $submission['phone'],
                $submission['project_location'],
                implode(', ', $names),
                $otherText,
                followUpLabel($submission['follow_up_method']),
                $submission['email'] ?? '',
                $submission['message'] ?? '',
                $submission['is_possible_duplicate'] ? 'Yes' : 'No',
            ];
        }

        return $rows;
    }
}
