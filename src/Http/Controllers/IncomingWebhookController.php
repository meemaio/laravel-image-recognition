<?php

namespace Meema\MediaRecognition\Http\Controllers;

use Aws\Sns\Message;
use Illuminate\Routing\Controller;

class IncomingWebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware('verify-signature');
    }

    /**
     * @throws \Exception
     */
    public function __invoke()
    {
        $message = json_decode(Message::fromRawPostData()['Message'], true);
        $detail = $message['detail'];
        $status = $detail['status'];

        try {
            $this->fireEventFor($status, $message);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param $status
     * @param $message
     * @throws \Exception
     */
    public function fireEventFor($status, $message)
    {
        switch ($status) {
            case 'PROGRESSING':
                event(new ConversionIsProgressing($message));
                break;
            case 'INPUT_INFORMATION':
                event(new ConversionHasInputInformation($message));
                break;
            case 'COMPLETE':
                event(new ConversionHasCompleted($message));
                break;
            case 'STATUS_UPDATE':
                event(new ConversionHasStatusUpdate($message));
                break;
            case 'NEW_WARNING':
                event(new ConversionHasNewWarning($message));
                break;
            case 'QUEUE_HOP':
                event(new ConversionQueueHop($message));
                break;
            case 'ERROR':
                event(new ConversionHasError($message));
                break;
            default:
                throw new \Exception();
        }
    }
}
