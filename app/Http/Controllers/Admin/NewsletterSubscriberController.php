<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = NewsletterSubscriber::query();

        if ($search) {
            $query->where('email', 'like', "%{$search}%");
        }

        if ($request->has('export')) {
            $selectedIds = $request->get('ids');
            if ($selectedIds) {
                $query->whereIn('id', explode(',', $selectedIds));
            }
            $records = $query->orderBy($sort, $order)->get();

            return response()->streamDownload(function () use ($records) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID', 'Email', 'Subscribed At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->email,
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'newsletter_subscribers_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $subscribers = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.newsletter-subscribers.index', compact('subscribers', 'search', 'limit', 'sort', 'order'));
    }

    public function destroy($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();
        return back()->with('success', 'Subscriber deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:newsletter_subscribers,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;

        if ($action === 'delete') {
            NewsletterSubscriber::whereIn('id', $ids)->delete();
            return redirect()->route('admin.newsletter-subscribers.index')->with('success', 'Selected newsletter subscribers deleted successfully.');
        }

        return redirect()->route('admin.newsletter-subscribers.index')->with('error', 'Invalid bulk action.');
    }
}
