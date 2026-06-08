<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Question;
use App\Models\Category;
use App\Models\Answer;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionImport;
use App\Exports\QuestionTemplateExport;
use App\Exports\QuestionExport;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::with('categories')->withCount('quizzes');
        
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('category_question.category_id', $request->category_id);
            });
        }
        
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $questions = $query->latest()->paginate($perPage)->withQueryString();
        $categories = Category::all();

        return Inertia::render('Admin/Question/Index', [
            'questions' => $questions,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'risk_level', 'per_page']),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:2048'
        ]);

        Excel::import(new QuestionImport, $request->file('file'));

        return redirect()->back()->with('success', 'Bank soal berhasil diimpor.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new QuestionTemplateExport, 'template_import_soal.xlsx');
    }

    public function export()
    {
        return Excel::download(new QuestionExport, 'data_bank_soal.xlsx');
    }

    public function create()
    {
        return Inertia::render('Admin/Question/Create', [
            'categories' => Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|max:2048',
            'risk_level' => 'required|in:Low,Medium,High',
            'reference' => 'nullable|string',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'answers' => 'required|array|min:2',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.answer_image' => 'nullable|image|max:2048',
            'answers.*.is_correct' => 'required|boolean'
        ]);

        $data = $request->only(['question_text', 'risk_level', 'reference']);
        if ($request->hasFile('question_image')) {
            $path = $request->file('question_image')->store('questions', 'public');
            $data['question_image'] = '/storage/' . $path;
        }
        
        $question = Question::create($data);
        
        $question->categories()->attach($request->categories);

        foreach ($request->answers as $ansData) {
            $ansCreate = [
                'answer_text' => $ansData['answer_text'] ?? null,
                'is_correct' => $ansData['is_correct'],
            ];
            
            if (isset($ansData['answer_image']) && $ansData['answer_image'] instanceof \Illuminate\Http\UploadedFile) {
                $path = $ansData['answer_image']->store('answers', 'public');
                $ansCreate['answer_image'] = '/storage/' . $path;
            }
            
            $question->answers()->create($ansCreate);
        }

        return redirect()->back()->with('success', 'Soal berhasil ditambahkan!');
    }

    public function edit(Question $question)
    {
        $question->load(['answers', 'categories']);
        return Inertia::render('Admin/Question/Edit', [
            'question' => $question,
            'categories' => Category::all()
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|max:2048',
            'remove_question_image' => 'nullable|boolean',
            'risk_level' => 'required|in:Low,Medium,High',
            'reference' => 'nullable|string',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'answers' => 'required|array|min:2',
            'answers.*.id' => 'required|exists:answers,id',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.answer_image' => 'nullable|image|max:2048',
            'answers.*.remove_answer_image' => 'nullable|boolean',
            'answers.*.is_correct' => 'required|boolean'
        ]);

        $data = $request->only(['question_text', 'risk_level', 'reference']);
        if ($request->hasFile('question_image')) {
            $path = $request->file('question_image')->store('questions', 'public');
            $data['question_image'] = '/storage/' . $path;
        } elseif ($request->boolean('remove_question_image')) {
            $data['question_image'] = null;
        }
        $question->update($data);
        
        $question->categories()->sync($request->categories);

        foreach ($request->answers as $ansData) {
            $answer = Answer::find($ansData['id']);
            $ansUpdate = [
                'answer_text' => $ansData['answer_text'] ?? null,
                'is_correct' => $ansData['is_correct'],
            ];
            
            if (isset($ansData['answer_image']) && $ansData['answer_image'] instanceof \Illuminate\Http\UploadedFile) {
                $path = $ansData['answer_image']->store('answers', 'public');
                $ansUpdate['answer_image'] = '/storage/' . $path;
            } elseif (isset($ansData['remove_answer_image']) && filter_var($ansData['remove_answer_image'], FILTER_VALIDATE_BOOLEAN)) {
                $ansUpdate['answer_image'] = null;
            }
            
            $answer->update($ansUpdate);
        }

        return redirect()->route('admin.questions.index')->with('success', 'Soal berhasil diperbarui.');
    }
    
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->back()->with('success', 'Soal berhasil dihapus dari Master Bank.');
    }
}
