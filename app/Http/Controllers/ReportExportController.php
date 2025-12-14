<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\ProjectReport;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Html;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportExportController extends Controller
{
    public function exportReport($id)
    {
        $quotation = Quotation::with(['client', 'materials', 'status'])->findOrFail($id);
        
        $reports = ProjectReport::where('quotation_id', $id)
                                ->orderBy('created_at', 'desc')
                                ->get();

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1000,
            'marginRight' => 1000,
        ]);

        // ===== HEADER =====
        $headerPath = public_path('Image/header.png');
        if (file_exists($headerPath)) {
            $section->addImage($headerPath, [
                'width' => 500,
                'height' => 120,
                'alignment' => Jc::CENTER
            ]);
        }
        $section->addTextBreak(2);

        // ===== TITLE =====
        $section->addText('PROJECT REPORT & ANALYTICS', ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);

        // ===== QUOTATION SUMMARY =====
        $section->addText('Quotation Summary', ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);

        $qStatus = strtolower($quotation->status->status_name ?? '');
        $statusText = ucfirst(str_replace('_', ' ', $qStatus));

        $section->addText("Subject: {$quotation->subject}", ['size' => 11]);
        $section->addText("Status: {$statusText}", ['size' => 11]);
        $section->addText("Date Created: " . $quotation->created_at->format('F d, Y'), ['size' => 11]);
        
        if (!empty($quotation->description)) {
            $section->addText("Description: {$quotation->description}", ['size' => 11]);
        }
        $section->addTextBreak(1);

        // ===== CLIENT INFORMATION =====
        $section->addText('Client Information', ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);

        $section->addText("Name: {$quotation->client->first_name} {$quotation->client->last_name}", ['size' => 11]);
        $section->addText("Contact: {$quotation->client->contact_no}", ['size' => 11]);
        $section->addText("Address: {$quotation->client->address}", ['size' => 11]);
        $section->addTextBreak(1);

        // ===== MATERIALS & PRICING =====
        $section->addText('Materials & Pricing', ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        // Header row
        $headerRowStyle = ['bgColor' => 'E7E6E6'];
        $table->addRow();
        $table->addCell(3000, $headerRowStyle)->addText('Material', ['bold' => true, 'size' => 11]);
        $table->addCell(1500, $headerRowStyle)->addText('Qty', ['bold' => true, 'size' => 11]);
        $table->addCell(1500, $headerRowStyle)->addText('Unit', ['bold' => true, 'size' => 11]);
        $table->addCell(1500, $headerRowStyle)->addText('Unit Price', ['bold' => true, 'size' => 11]);
        $table->addCell(1500, $headerRowStyle)->addText('Total', ['bold' => true, 'size' => 11]);

        $totalMaterial = 0;
        foreach ($quotation->materials as $material) {
            $quantity = $material->pivot->quantity ?? 0;
            $unitPrice = $material->unit_price;
            $lineTotal = $unitPrice * $quantity;
            $totalMaterial += $lineTotal;

            $table->addRow();
            $table->addCell(3000)->addText($material->name, ['size' => 11]);
            $table->addCell(1500)->addText((string)$quantity, ['size' => 11]);
            $table->addCell(1500)->addText($material->unit, ['size' => 11]);
            $table->addCell(1500)->addText('₱' . number_format($unitPrice, 2), ['size' => 11], ['alignment' => Jc::RIGHT]);
            $table->addCell(1500)->addText('₱' . number_format($lineTotal, 2), ['size' => 11], ['alignment' => Jc::RIGHT]);
        }

        // Totals row
        $laborFee = $quotation->labor_fee ?? 0;
        $deliveryFee = $quotation->delivery_fee ?? 0;
        $grandTotal = $totalMaterial + $laborFee + $deliveryFee;

        $table->addRow();
        $table->addCell(7500, $headerRowStyle)->addText('Total Material Cost', ['bold' => true, 'size' => 11], ['alignment' => Jc::RIGHT]);
        $table->addCell(1500, $headerRowStyle)->addText('₱' . number_format($totalMaterial, 2), ['bold' => true, 'size' => 11], ['alignment' => Jc::RIGHT]);

        $table->addRow();
        $table->addCell(7500, $headerRowStyle)->addText('Labor Fee', ['bold' => true, 'size' => 11], ['alignment' => Jc::RIGHT]);
        $table->addCell(1500, $headerRowStyle)->addText('₱' . number_format($laborFee, 2), ['bold' => true, 'size' => 11], ['alignment' => Jc::RIGHT]);

        $table->addRow();
        $table->addCell(7500, $headerRowStyle)->addText('Delivery Fee', ['bold' => true, 'size' => 11], ['alignment' => Jc::RIGHT]);
        $table->addCell(1500, $headerRowStyle)->addText('₱' . number_format($deliveryFee, 2), ['bold' => true, 'size' => 11], ['alignment' => Jc::RIGHT]);

        $table->addRow();
        $totalRowStyle = ['bgColor' => 'D3D3D3'];
        $table->addCell(7500, $totalRowStyle)->addText('GRAND TOTAL', ['bold' => true, 'size' => 12], ['alignment' => Jc::RIGHT]);
        $table->addCell(1500, $totalRowStyle)->addText('₱' . number_format($grandTotal, 2), ['bold' => true, 'size' => 12], ['alignment' => Jc::RIGHT]);

        $section->addTextBreak(2);

        // ===== PROJECT TIMELINE =====
        if ($quotation->project_start_date && $quotation->project_end_date) {
            $section->addText('Project Timeline', ['bold' => true, 'size' => 14]);
            $section->addTextBreak(1);

            $startDate = Carbon::parse($quotation->project_start_date);
            $endDate = Carbon::parse($quotation->project_end_date);
            $today = Carbon::now();
            
            if ($today->greaterThanOrEqualTo($startDate) && $today->lessThanOrEqualTo($endDate)) {
                $projectStatus = 'Project In Progress';
            } elseif ($today->greaterThan($endDate)) {
                $projectStatus = 'Project Past End Date';
            } else {
                $projectStatus = 'Project Not Started';
            }

            $section->addText("Start Date: " . $startDate->format('F d, Y'), ['size' => 11]);
            $section->addText("End Date: " . $endDate->format('F d, Y'), ['size' => 11]);
            $section->addText("Status: {$projectStatus}", ['size' => 11]);
            $section->addTextBreak(1);
        }

        // ===== PROGRESS TRACKING =====
        $section->addText('Progress Tracking', ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);

        $latestReport = $reports->first();
        $currentProgress = $latestReport ? $latestReport->progress : 0;

        $section->addText("Current Progress: {$currentProgress}%", ['size' => 11]);
        if (!empty($latestReport) && !empty($latestReport->report)) {
            $section->addText("Latest Report: {$latestReport->report}", ['size' => 11]);
        }
        $section->addTextBreak(2);

        // ===== PROGRESS REPORT HISTORY =====
        if ($reports->count() > 0) {
            $section->addText('Progress Report History', ['bold' => true, 'size' => 14]);
            $section->addTextBreak(1);

            $historyTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
            
            // Header row
            $historyTable->addRow();
            $historyTable->addCell(2000, $headerRowStyle)->addText('Date', ['bold' => true, 'size' => 11]);
            $historyTable->addCell(1500, $headerRowStyle)->addText('Progress', ['bold' => true, 'size' => 11]);
            $historyTable->addCell(4000, $headerRowStyle)->addText('Report', ['bold' => true, 'size' => 11]);

            foreach ($reports as $report) {
                $historyTable->addRow();
                $historyTable->addCell(2000)->addText($report->created_at->format('M d, Y'), ['size' => 10]);
                $historyTable->addCell(1500)->addText($report->progress . '%', ['size' => 10], ['alignment' => Jc::CENTER]);
                $historyTable->addCell(4000)->addText($report->report ?? '—', ['size' => 10]);
            }

            $section->addTextBreak(2);
        }

        // ===== FOOTER =====
        $section->addTextBreak(2);
        $section->addText("Generated on: " . now()->format('F d, Y H:i A'), ['italic' => true, 'size' => 10], ['alignment' => Jc::CENTER]);

        // ===== SAVE & DOWNLOAD =====
        $filename = 'Report_' . $quotation->id . '_' . date('Y-m-d_His') . '.docx';
        $path = storage_path('app/exports/' . $filename);

        // Create exports directory if it doesn't exist
        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        // Debug: log file size
        clearstatcache(true, $path);
        $size = file_exists($path) ? filesize($path) : 0;
        if ($size < 1000) {
            Log::warning("Exported report docx file is very small ($size bytes): $path");
        }

        // Only download if file exists and is non-empty
        if (!file_exists($path) || $size < 1000) {
            return response()->json(['error' => 'Export failed, file not created or too small.'], 500);
        }

        Log::info('Report exported', ['quotation_id' => $id, 'filename' => $filename]);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
