<?php

namespace App\Mail;

use App\Models\Documents;
use App\Models\DocumentApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $approval;

    public function __construct(Documents $document, DocumentApproval $approval)
    {
        $this->document = $document;
        $this->approval = $approval;
    }

    public function build()
    {
        $subject = $this->document->email_subject 
            ? $this->document->email_subject 
            : 'Document Approval Required: ' . $this->document->document_name;

        $messageContent = $this->document->email_message;

        $viewData = [
            'docName'     => $this->document->document_name,
            'order'       => $this->approval->approver_order,
            'approval'    => $this->approval,
            'document'    => $this->document,
            'customMessage' => $messageContent,   // Untuk digunakan di view
        ];

        return $this->subject($subject)
                    ->view('emails.document_approval')
                    ->with($viewData);
    }
}