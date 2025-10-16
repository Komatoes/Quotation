<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Show the project report page.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function viewReport($id)
    {
        $project = Project::with(['client', 'materials', 'progressLogs'])->findOrFail($id);
        return view('view-report', compact('project'));
    }


    /**
     * Display a listing of projects.
     */
    public function index()
    {
        // Example: fetch all projects
        $projects = Project::with('client')->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        // Validation and storing logic here
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        // Update logic here
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        // Delete logic here
    }


    // NEWWWWWWWWWWWWWWWWWWWWWWWW
    /**
     * Update the progress of a specific project.
     */
    public function updateProgress(Request $request, $quotationId)
    {
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100',
            'latest_progress_report' => 'nullable|string|max:255',
        ]);

        $project = Project::where('quotation_id', $quotationId)->firstOrFail();

        // Update progress
        $project->progress = $request->input('progress');
        $project->latest_progress_report = $request->input('latest_progress_report');
        $project->save();

        // Optionally, log the progress update
        $project->progressLogs()->create([
            'progress' => $request->input('progress'),
            'note' => $request->input('latest_progress_report'),
        ]);

        return response()->json(['success' => true, 'message' => 'Project progress updated successfully.']);
    }


}
