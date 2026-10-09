<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = ContactInquiry::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
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
                fputcsv($file, ['ID', 'First Name', 'Last Name', 'Phone', 'Email', 'Subject', 'Message', 'Submitted At']);
                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->first_name,
                        $row->last_name,
                        $row->phone,
                        $row->email,
                        $row->subject,
                        $row->message,
                        $row->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            }, 'contact_inquiries_export_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
        }

        $inquiries = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.contact-inquiries.index', compact('inquiries', 'search', 'limit', 'sort', 'order'));
    }

    public function destroy($id)
    {
        $inquiry = ContactInquiry::findOrFail($id);
        $inquiry->delete();
        return back()->with('success', 'Contact inquiry deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:contact_inquiries,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;

        if ($action === 'delete') {
            ContactInquiry::whereIn('id', $ids)->delete();
            return redirect()->route('admin.contact-inquiries.index')->with('success', 'Selected contact inquiries deleted successfully.');
        }

        return redirect()->route('admin.contact-inquiries.index')->with('error', 'Invalid bulk action.');
    }
}
