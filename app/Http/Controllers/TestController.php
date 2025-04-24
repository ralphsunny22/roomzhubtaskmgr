<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MailchimpService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function mailchimp()
    {
        return '223';
    }

    public function addSubscriber(MailchimpService $mailchimp, $email, $name = null)
    {
        $mailchimp->subscribe($email, [
            'FNAME' => $name ?? 'User'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function sendViaMailchimp(MailchimpService $mailchimp)
    {
        // 1. Add user to list (if not already added)
        // $this->addSubscriber($mailchimp, 'ralphdns@yahoo.com', 'Santos');
        $this->addSubscriber($mailchimp, 'ralphsunny114@gmail.com', 'Ralph Santos');

        // $html = View::make('email.test-mail', ['name' => 'John Doe'])->render();
        $html = View::make('email.test-mail', [
            'name' => 'Jane Doe',
            'resetLink' => 'https://yourapp.com/reset-password/token123'
        ])->render();

        $campaign = $mailchimp->createCampaign('Hello from Laravel', 'My App', 'noreply@myapp.com');

        $mailchimp->setCampaignContent($campaign->id, $html);
        $mailchimp->sendCampaign($campaign->id);

        return 'Mailchimp campaign sent!';
        // return response()->json(['message' => 'Mailchimp campaign sent!']);
    }


    /**
     * Store a newly created resource in storage.
     */

     public function sendForgotPasswordMail()
     {
         Mail::to('ralphsunny114@gmail.com')->send(new TestMail('Jane Doe', 'https://roomzhub.com/reset-password/token123'));
         return '123';

        //  return response()->json(['message' => 'Password reset mail sent via Mailgun sandbox']);
     }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
