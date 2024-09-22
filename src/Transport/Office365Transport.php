<?php

namespace Socialsquared\Office365mailer\Transport;

use Microsoft\Graph\Generated\Models\BodyType;
use Microsoft\Graph\Generated\Models\EmailAddress;
use Microsoft\Graph\Generated\Models\ItemBody;
use Microsoft\Graph\Generated\Models\Message;
use Microsoft\Graph\Generated\Models\Recipient;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Exception;
use GuzzleHttp\Psr7\Utils;
use League\OAuth2\Client\Grant\ClientCredentials;
use Microsoft\Graph\Generated\Models\FileAttachment;

class Office365Transport extends AbstractTransport
{

    protected GraphServiceClient $graph;

    public function __construct()
    {
        $tokenRequestContext = new ClientCredentialContext(
            env('OFFICE365MAIL_TENANT'),
            env('OFFICE365MAIL_CLIENT_ID'),
            env('OFFICE365MAIL_SECRET')
        );
        $this->graph = new GraphServiceClient($tokenRequestContext);
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        try {
            $sender = new EmailAddress();
            $sender->setAddress($email->getFrom()[0]->getAddress());
            $sender->setName($email->getFrom()[0]->getName());
            $fromRecipient = new Recipient();
            $fromRecipient->setEmailAddress($sender);

            $recipients = [];

            foreach ($email->getTo() as $to) {
                $recipientEmail = new EmailAddress();
                $recipientEmail->setAddress($to->getAddress());
                $recipientEmail->setName($to->getName());
                $toRecipient = new Recipient();
                $toRecipient->setEmailAddress($recipientEmail);
                $recipients[] = $toRecipient;
            }

            $emailBody = new ItemBody();

            if ($email->getTextBody()) {
                $emailBody->setContent($email->getTextBody());
                $emailBody->setContentType(new BodyType(BodyType::TEXT));
            } else {
                $emailBody->setContent($email->getHtmlBody());
                $emailBody->setContentType(new BodyType(BodyType::HTML));
            }

            $message = new Message();
            $message->setSubject($email->getSubject());
            $message->setFrom($fromRecipient);
            $message->setToRecipients($recipients);
            $message->setBody($emailBody);

            if ($email->getCc()) {
                $ccRecipients = [];
                foreach ($email->getCc() as $cc) {
                    $ccEmail = new EmailAddress();
                    $ccEmail->setAddress($cc->getAddress());
                    $ccEmail->setName($cc->getName());
                    $ccRecipient = new Recipient();
                    $ccRecipient->setEmailAddress($ccEmail);
                    $ccRecipients[] = $ccRecipient;
                }
                $message->setCcRecipients($ccRecipients);
            }

            if ($email->getBcc()) {
                $bccRecipients = [];
                foreach ($email->getBcc() as $bcc) {
                    $bccEmail = new EmailAddress();
                    $bccEmail->setAddress($bcc->getAddress());
                    $bccEmail->setName($bcc->getName());
                    $bccRecipient = new Recipient();
                    $bccRecipient->setEmailAddress($bccEmail);
                    $bccRecipients[] = $bccRecipient;
                }
                $message->setBccRecipients($bccRecipients);
            }

            if ($email->getAttachments()) {
                $attachments = [];
                foreach ($email->getAttachments() as $attachment) {
                    $attachmentsAttachment1 = new FileAttachment();
                    $attachmentsAttachment1->setOdataType('#microsoft.graph.fileAttachment');
                    $attachmentsAttachment1->setName($attachment->getFilename());
                    $attachmentsAttachment1->setContentType($attachment->getContentType());
                    $attachmentsAttachment1->setContentBytes(Utils::streamFor(base64_encode($attachment->getBody())));
                    $attachments[] = $attachmentsAttachment1;
                }
                $message->setAttachments($attachments);
            }

            $requestBody = new SendMailPostRequestBody();
            $requestBody->setMessage($message);

            $this->graph->users()->byUserId($email->getFrom()[0]->getAddress())->sendMail()->post($requestBody)->wait();
        } catch (Exception $e) {
            throw new \RuntimeException('Unable to send email: ' . $e);
        } 
    }

    public function __toString(): string
    {
        return 'office365';
    }
}
