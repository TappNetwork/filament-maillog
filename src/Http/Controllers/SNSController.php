<?php

namespace Tapp\FilamentMailLog\Http\Controllers;

use App\Models\User;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Tapp\FilamentMailLog\Models\MailLog;

class SNSController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->json()->all();
        // Start verification
        if ($data['Type'] == 'SubscriptionConfirmation') {
            file_get_contents($data['SubscribeURL']);  // To verify. (When the first request comes from AWS SNS)

            return;
        }
        // End verification

        $message = Message::fromRawPostData();
        $validator = new MessageValidator;

        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            Log::error('SNS Message Validation Error: '.$e->getMessage());
        }

        $messageBody = json_decode($message->offsetGet('Message'), true);
        // Remove once we store all the data we need
        $uniqueId = $this->getUniqueIdFromHeader($messageBody);

        $notificationLog = MailLog::where('message_id', $uniqueId)->first();

        // Log the message body
        Log::info('SNS Message ID: '.$uniqueId);
        Log::info('SNS Message Body: '.json_encode($messageBody));

        if (! $notificationLog) {
            return response()->json([], Response::HTTP_OK);
        }

        $notificationLog->update([
            'data' => $messageBody,
        ]);

        $notificationLog->update([
            'status' => $messageBody['eventType'],
        ]);

        if ($messageBody['eventType'] == 'Delivery') {
            $notificationLog->update([
                'delivered' => now(),
            ]);
        } elseif ($messageBody['eventType'] == 'Bounce') {
            $notificationLog->update([
                'bounced' => now(),
            ]);

            /** @phpstan-ignore-next-line */
            $user = User::where('email', $notificationLog->to_address)->first();
            if ($user) {
                $user->contact_method_email = false;
                $user->save();
                Log::info($user->email.' unsubscribed from notifications');
            }
        } elseif ($messageBody['eventType'] == 'Complaint') {
            $notificationLog->update([
                'complaint' => now(),
            ]);
        }

        return response()->json([], Response::HTTP_OK);
    }

    private function getUniqueIdFromHeader($messageBody)
    {
        return collect($messageBody['mail']['headers'])->filter(function ($header) {
            return $header['name'] === 'unique-id';
        })->map(function ($header) {
            return $header['value'];
        })->first();
    }
}
