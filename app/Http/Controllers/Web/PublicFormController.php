<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicFormController extends Controller
{
    public function submitContact(Request $request)
    {
        if ($request->has('name') && (!$request->has('first_name') || !$request->input('first_name'))) {
            $nameParts = explode(' ', trim($request->input('name')), 2);
            $request->merge([
                'first_name' => $nameParts[0] ?? 'User',
                'last_name' => $nameParts[1] ?? 'User',
            ]);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'phone'      => 'required|digits:10',
            'email'      => 'required|email|max:255',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ], [
            'phone.required' => 'Please enter your 10-digit mobile number.',
            'phone.digits'   => 'Mobile number must be exactly 10 digits.',
        ]);

        ContactInquiry::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name') ?? '',
            'phone' => $request->input('phone') ?? '',
            'email' => $request->input('email'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
        ]);

        return back()->with('success', 'Thank you! Your message has been sent successfully. We will get back to you soon.');
    }

    public function subscribeNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email address.'
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));

        // Check if already subscribed to avoid duplicate entry error
        $subscriber = NewsletterSubscriber::where('email', $email)->first();
        if (!$subscriber) {
            NewsletterSubscriber::create(['email' => $email]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing to our newsletter!'
        ]);
    }
}
