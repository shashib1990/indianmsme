<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTopic;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $topics = FaqTopic::orderBy('sort_order', 'asc')->get();

        $query = Faq::with('topic')->orderBy('sort_order', 'asc')->orderBy('id', 'desc');

        if ($request->filled('topic_id')) {
            $query->where('faq_topic_id', $request->topic_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->paginate(20)->withQueryString();

        return view('admin.faqs.index', compact('faqs', 'topics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faq_topic_id' => 'required|exists:faq_topics,id',
            'question' => 'required|string|max:1000',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        Faq::create($validated);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ Question added successfully.');
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'faq_topic_id' => 'required|exists:faq_topics,id',
            'question' => 'required|string|max:1000',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $faq->update($validated);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ Question updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('admin.faqs.index')->with('success', 'FAQ Question deleted successfully.');
    }

    public function toggleStatus(Faq $faq)
    {
        $faq->update(['is_active' => !$faq->is_active]);
        return response()->json([
            'success' => true,
            'is_active' => $faq->is_active,
            'message' => 'FAQ status updated successfully.'
        ]);
    }
}
