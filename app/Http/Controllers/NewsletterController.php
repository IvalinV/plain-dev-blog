<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscribeRequest;
use Illuminate\Support\Arr;
use Spatie\Newsletter\Facades\Newsletter;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request)
    {
        $email = Arr::get($request->validated(), 'email');

        $subscribed = Newsletter::subscribe($email);

        if (! $subscribed) {
            abort(500, 'Something went wrong');
        }

        return response()->json(['message' => 'Email subscribed succesfuly']);
    }
}
