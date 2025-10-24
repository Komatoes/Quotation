<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Http\Response;

class QuotationExportController extends Controller
{
    public function export($id)
    {
        $quotation = Quotation::with(['client', 'materials'])->findOrFail($id);
        
        // Create new Word document
        $phpWord = new PhpWord();
        
        // Add styles
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14]);
        
        // Add a section
        $section = $phpWord->addSection();
        
        // Add company header (you can customize this)
        $section->addText('QUOTATION', ['bold' => true, 'size' => 24], ['alignment' => 'center']);
        $section->addTextBreak(1);
        
        // Add quotation details
        $section->addTitle("Quotation Details", 1);
        $section->addText("Subject: {$quotation->subject}");
        $section->addText("Date: " . $quotation->created_at->format('F d, Y'));
        $section->addTextBreak(1);
        
        // Add client information
        $section->addTitle("Client Information", 1);
        $section->addText("Name: {$quotation->client->first_name} {$quotation->client->last_name}");
        $section->addText("Contact: {$quotation->client->contact_no}");
        $section->addText("Address: {$quotation->client->address}");
        $section->addTextBreak(1);
        
        // Add materials table
        $section->addTitle("Materials", 1);
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        // Add header row
        $table->addRow();
        $table->addCell(2500)->addText('Material', ['bold' => true]);
        $table->addCell(1500)->addText('Quantity', ['bold' => true]);
        $table->addCell(1500)->addText('Unit Price', ['bold' => true]);
        $table->addCell(1500)->addText('Total', ['bold' => true]);
        
        // Add material rows
        $total = 0;
        foreach ($quotation->materials as $material) {
            $lineTotal = $material->unit_price * $material->pivot->quantity;
            $total += $lineTotal;
            
            $table->addRow();
            $table->addCell(2500)->addText($material->name);
            $table->addCell(1500)->addText($material->pivot->quantity . ' ' . $material->unit);
            $table->addCell(1500)->addText('₱' . number_format($material->unit_price, 2));
            $table->addCell(1500)->addText('₱' . number_format($lineTotal, 2));
        }
        
        $section->addTextBreak(1);
        
        // Add fees and total
        $section->addText('Labor Fee: ₱' . number_format($quotation->labor_fee, 2), ['bold' => true]);
        $section->addText('Delivery/Hauling Fee: ₱' . number_format($quotation->delivery_fee, 2), ['bold' => true]);
        $section->addText('Grand Total: ₱' . number_format($total + $quotation->labor_fee + $quotation->delivery_fee, 2), 
            ['bold' => true, 'size' => 14]);
        
        // Generate the file
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        
        // Save file in storage
        $filename = 'Quotation_' . $quotation->id . '_' . date('Y-m-d') . '.docx';
        $writer->save(storage_path('app/public/' . $filename));
        
        // Return the file as download
        return response()->download(storage_path('app/public/' . $filename))->deleteFileAfterSend(true);
    }
}