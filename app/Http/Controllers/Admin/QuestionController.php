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

        $questions = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::all();

        return Inertia::render('Admin/Question/Index', [
            'questions' => $questions,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'risk_level']),
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
            'risk_level' => 'required|in:Low,Medium,High',
            'reference' => 'nullable|string',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'answers' => 'required|array|min:2',
            'answers.*.id' => 'required|exists:answers,id',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.answer_image' => 'nullable|image|max:2048',
            'answers.*.is_correct' => 'required|boolean'
        ]);

        $data = $request->only(['question_text', 'risk_level', 'reference']);
        if ($request->hasFile('question_image')) {
            $path = $request->file('question_image')->store('questions', 'public');
            $data['question_image'] = '/storage/' . $path;
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
