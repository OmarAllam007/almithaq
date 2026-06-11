<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class StaticPagesController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('Landing/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Landing/Contact');
    }

    public function privacy(): Response
    {
        return Inertia::render('Landing/Privacy');
    }

    public function terms(): Response
    {
        return Inertia::render('Landing/Terms');
    }

    public function sendContact(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Mail::raw(
            implode("\n", [
                "Name: {$data['name']}",
                "Email: {$data['email']}",
                "Subject: {$data['subject']}",
                '',
                $data['message'],
            ]),
            function ($message) use ($data): void {
                $message
                    ->to('contact@khotobah.com')
                    ->replyTo($data['email'], $data['name'])
                    ->subject("[Khotobah Contact] {$data['subject']}");
            }
        );

        return back()->with('success', true);
    }
}
