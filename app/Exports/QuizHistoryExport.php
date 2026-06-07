<?php

namespace App\Exports;

use App\Models\QuizAttempt;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuizHistoryExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $month;
    protected $sortField;
    protected $sortDirection;

    public function __construct(?string $search, ?string $month, string $sortField = 'tanggal', string $sortDirection = 'desc')
    {
        $this->search = $search;
        $this->month = $month;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
    }

    public function query()
    {
        $sortMap = [
            'petugas' => 'users.name',
            'nip' => 'users.nip',
            'tanggal' => 'quiz_attempts.created_at',
            'kuis' => 'quizzes.title',
            'score' => 'quiz_attempts.score',
            'correct_answers' => 'quiz_attempts.correct_answers',
            'durasi' => 'quiz_attempts.time_ms',
        ];
        $orderColumn = $sortMap[$this->sortField] ?? 'quiz_attempts.created_at';
        $orderDirection = in_array(strtolower($this->sortDirection), ['asc', 'desc']) ? $this->sortDirection : 'desc';
        $query = QuizAttempt::query()
            ->select('quiz_attempts.*')
            ->join('users', 'quiz_attempts.user_id', '=', 'users.id')
            ->leftJoin('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->with(['user', 'quiz']);
        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%$search%")
                  ->orWhere('users.nip', 'like', "%$search%");
            });
        }
        if (!empty($this->month)) {
            $query->where('quiz_attempts.month_year', $this->month);
        }
        $query->orderBy($orderColumn, $orderDirection);
        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Petugas',
            'NIP',
            'Tanggal',
            'Nama Kuis',
            'Skor',
            'Jawaban Benar',
            'Durasi',
        ];
    }

    private int $rowNumber = 0;

    public function map($attempt): array
    {
        $this->rowNumber++;
        $seconds = floor($attempt->time_ms / 1000);
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        $durasi = "{$minutes}m {$remainingSeconds}s";
        $tanggal = $attempt->created_at ? $attempt->created_at->format('d M Y, H:i') : '-';
        return [
            $this->rowNumber,
            $attempt->user ? $attempt->user->name : 'Unknown User',
            $attempt->user ? $attempt->user->nip : '-',
            $tanggal,
            $attempt->quiz ? $attempt->quiz->title : 'Kuis dihapus',
            $attempt->score,
            $attempt->correct_answers,
            $durasi,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
