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
        return $this->subject(' Dokumen Menunggu Persetujuan: ' . $this->document->document_name)
                    ->view('emails.document_approval') // Pastikan view ini ada di resources/views/emails/
                    ->with([
                        'docName' => $this->document->document_name,
                        'order' => $this->approval->approver_order,
                    ]);
    }
}