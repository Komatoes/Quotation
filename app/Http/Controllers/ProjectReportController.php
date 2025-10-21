<?php

namespace App\Http\Controllers;

use App\Models\ProjectReport;
use Illuminate\Http\Request;
use App\Models\Quotation;

class ProjectReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProjectReport  $projectReport
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProjectReport  $projectReport
     * @return \Illuminate\Http\Response
     */
    public function edit(ProjectReport $projectReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProjectReport  $projectReport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProjectReport $projectReport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProjectReport  $projectReport
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProjectReport $projectReport)
    {
        //
    }

    public function updateProgress(Request $request, $quotationId)
    {
        // 1. Validate the input
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100',
            'report' => 'nullable|string|max:255', // Use 'report' to match DB/Model
        ]);

        // 2. Find the current highest progress for this quotation
        $latestReport = ProjectReport::where('quotation_id', $quotationId)
                                    ->orderBy('progress', 'desc')
                                    ->first();

        $currentProgress = $latestReport ? $latestReport->progress : 0;
        $newProgress = $request->input('progress');

        // 3. **Non-Reversion Logic**: Check if the new progress is less than the current highest
        if ($newProgress < $currentProgress) {
            return response()->json([
                'success' => false,
                'message' => "Progress cannot be reverted. Current saved progress is {$currentProgress}%."
            ], 400); // Return a 400 Bad Request status
        }

        // 4. Create a new progress report record (instead of updating a single 'project' column)
        ProjectReport::create([
            'quotation_id' => $quotationId,
            'progress' => $newProgress,
            'report' => $request->input('report'), // Use 'report'
        ]);

        // 5. Update the parent Quotation/Project with the latest progress for quick display
        // NOTE: If you have a separate 'projects' table, you should update it here.
        // Assuming you want to update the latest progress on the Quotation model:
        $quotation = Quotation::findOrFail($quotationId);
        $quotation->latest_progress = $newProgress; // Assuming you have a `latest_progress` column on Quotation
        $quotation->save();


        return response()->json(['success' => true, 'message' => 'Project progress updated and locked successfully.']);
    }


    // ... inside your controller class ...

  public function show($id)
{
    $quotation = Quotation::with(['client', 'materials', 'progressReports'])->findOrFail($id);
    
    // Retrieve all progress reports ordered by latest first
    $reports = $quotation->progressReports()->orderBy('created_at', 'desc')->get();
    
    // Pass both quotation and reports to the blade view
    return view('view-report', compact('quotation', 'reports'));
}



}
