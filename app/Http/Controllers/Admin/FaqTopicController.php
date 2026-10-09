<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqTopicController extends Controller
{
    public function index()
    {
        $topics = FaqTopic::withCount('faqs')->orderBy('sort_order', 'asc')->get();
        return view('admin.faqs.topics.index', compact('topics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:faq_topics,slug',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        FaqTopic::create($validated);

        return redirect()->route('admin.faq-topics.index')->with('success', 'FAQ Topic created successfully.');
    }

    public function update(Request $request, FaqTopic $faqTopic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:faq_topics,slug,' . $faqTopic->id,
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $faqTopic->update($validated);

        return redirect()->route('admin.faq-topics.index')->with('success', 'FAQ Topic updated successfully.');
    }

    public function destroy(FaqTopic $faqTopic)
    {
        $faqTopic->delete();
        return redirect()->route('admin.faq-topics.index')->with('success', 'FAQ Topic deleted successfully.');
    }

    public function toggleStatus(FaqTopic $faqTopic)
    {
        $faqTopic->update(['is_active' => !$faqTopic->is_active]);
        return response()->json([
            'success' => true,
            'is_active' => $faqTopic->is_active,
            'message' => 'Topic status updated successfully.'
        ]);
    }
}
