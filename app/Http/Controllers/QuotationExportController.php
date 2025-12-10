<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use Illuminate\Http\Response;
use PhpOffice\PhpWord\SimpleType\Jc;

class QuotationExportController extends Controller
{
    public function export($id)
    {
        $quotation = Quotation::with(['client', 'materials'])->findOrFail($id);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // HEADER: single image at top
        $headerPath = public_path('Image/header.png');
        if (file_exists($headerPath)) {
            $section->addImage($headerPath, [
                'width' => 400,
                'height' => 100,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
        }
        $section->addTextBreak(2);

        // QUOTATION TITLE
        $section->addText('QUOTATION DETAILS', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        $section->addTextBreak(1);

        // Quotation details
        $section->addText("Subject: {$quotation->subject}");
        $section->addText("Date: " . $quotation->created_at->format('F d, Y'));
        $section->addText("Description: {$quotation->description}");
        $section->addTextBreak(1);

        // Client Info
        $section->addText('Client Information', ['bold' => true, 'size' => 14]);
        $section->addText("Name: {$quotation->client->first_name} {$quotation->client->last_name}");
        $section->addText("Contact: {$quotation->client->contact_no}");
        $section->addText("Address: {$quotation->client->address}");
        $section->addTextBreak(1);

        // Materials
        $section->addText('Materials', ['bold' => true, 'size' => 12]);
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $table->addRow();
        $table->addCell(4000)->addText('Material', ['bold' => true]);
        $table->addCell(2000)->addText('Quantity', ['bold' => true]);
        $table->addCell(2000)->addText('Unit Price', ['bold' => true]);
        $table->addCell(2000)->addText('Total', ['bold' => true]);

        $total = 0;
        foreach ($quotation->materials as $material) {
            $lineTotal = $material->unit_price * $material->pivot->quantity;
            $total += $lineTotal;

            $table->addRow();
            $table->addCell(4000)->addText($material->name);
            $table->addCell(2000)->addText($material->pivot->quantity . ' ' . $material->unit);
            $table->addCell(2000)->addText('₱' . number_format($material->unit_price, 2));
            $table->addCell(2000)->addText('₱' . number_format($lineTotal, 2));
        }

        $section->addTextBreak(1);

        $fontStyle = ['bold' => true];
        $rightAlign = ['alignment' => Jc::RIGHT];

        $section->addText("Labor Fee: ₱" . number_format($quotation->labor_fee, 2), $fontStyle, $rightAlign);
        $section->addText("Delivery/Hauling Fee: ₱" . number_format($quotation->delivery_fee, 2), $fontStyle, $rightAlign);
        $section->addText("Grand Total: ₱" . number_format($total + $quotation->labor_fee + $quotation->delivery_fee, 2), $fontStyle, $rightAlign);


        $section->addTextBreak(2);
        $section->addText("Thank you for giving us the opportunity to write this quotation for this project.");
        $section->addText("Respectfully yours,");
        $section->addTextBreak(2);

        // E-Signature image and name
        $signaturePath = public_path('Image/e_signature.png');
        if (file_exists($signaturePath)) {
            $section->addImage($signaturePath, [
                'width' => 120,
                'height' => 40,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
            ]);
        } else {
            $section->addText("__________________________", ['bold' => true]);
        }
        $section->addText("Jomilo Laño", ['bold' => true]);
        $section->addText("JOM’S Construction Services");

        // Save and return
        $filename = 'Quotation_' . $quotation->id . '_' . date('Y-m-d') . '.docx';
        $path = storage_path('app/public/' . $filename);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        // Debug: log file size
        clearstatcache(true, $path);
        $size = file_exists($path) ? filesize($path) : 0;
        if ($size < 1000) {
            \Log::warning("Exported docx file is very small ($size bytes): $path");
        }

        // Only download if file exists and is non-empty
        if (!file_exists($path) || $size < 1000) {
            return response()->json(['error' => 'Export failed, file not created or too small.'], 500);
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Export a quotation by its public token (for public links).
     * Delegates to the existing export method after resolving the quotation id.
     */
    public function exportByToken($token)
    {
        $quotation = Quotation::where('public_token', $token)->firstOrFail();
        return $this->export($quotation->id);
    }
}
