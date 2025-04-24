<?php

namespace App\Services;

use MailchimpMarketing\ApiClient;

class MailchimpService
{
    protected $client;

    public function __construct()
    {
        $this->client = new ApiClient();
        $this->client->setConfig([
            'apiKey' => config('services.mailchimp.key'),
            'server' => config('services.mailchimp.server'),
        ]);
    }

    public function subscribe(string $email, array $mergeFields = [])
    {
        return $this->client->lists->setListMember(
            config('services.mailchimp.list_id'),
            md5(strtolower($email)), // Mailchimp requires subscriber hash (lowercase MD5 of email)
            [
                'email_address' => $email,
                'status_if_new' => 'subscribed',
                'status' => 'subscribed',
                'merge_fields' => $mergeFields,
            ]
        );
    }


    public function createCampaign($subject, $fromName, $replyTo)
    {
        return $this->client->campaigns->create([
            "type" => "regular",
            "recipients" => [
                "list_id" => config('services.mailchimp.list_id'),
            ],
            "settings" => [
                "subject_line" => $subject,
                "title" => "Campaign: $subject",
                "from_name" => $fromName,
                "reply_to" => $replyTo,
            ]
        ]);
    }

    public function setCampaignContent($campaignId, $html)
    {
        return $this->client->campaigns->setContent($campaignId, [
            "html" => $html
        ]);
    }

    public function sendCampaign1($campaignId)
    {
        return $this->client->campaigns->send($campaignId);
    }

    public function sendCampaign($campaignId)
    {
        $campaign = $this->client->campaigns->get($campaignId);
        // $campaignDetails = $this->client->campaigns->get($campaign->id);
        // dd($campaign);

        // if ($campaign->status !== 'save') {
        //     throw new \Exception("Campaign not ready to send. Status: {$campaign->status}");
        // }

        return $this->client->campaigns->send($campaignId);
    }



}
