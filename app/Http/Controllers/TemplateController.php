<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = ReportTemplate::withCount('generatedReports')->latest()->get();
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $defaultSections = [
            'cover', 'executive_summary', 'kpi_summary',
            'position_chart', 'distribution_chart', 'top_keywords',
            'landing_pages', 'competitor_monitoring', 'action_items',
        ];
        return view('templates.create', compact('defaultSections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'cover_title'     => 'nullable|string|max:255',
            'agency_name'     => 'nullable|string|max:255',
            'primary_color'   => ['required','string','regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required','string','regex:/^#[0-9a-fA-F]{6}$/'],
            'is_default'      => 'boolean',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('templates/logos', 'public');
        }

        $defaultSections = [
            'cover','executive_summary','kpi_summary','position_chart',
            'distribution_chart','top_keywords','landing_pages',
            'competitor_monitoring','action_items',
        ];
        $validated['layout_config_json'] = ['sections' => $defaultSections];

        if ($request->boolean('is_default')) {
            ReportTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        unset($validated['logo']);
        $validated['is_default'] = $request->boolean('is_default');

        ReportTemplate::create($validated);

        return redirect()->route('templates.index')->with('success', 'Template đã được tạo.');
    }

    public function edit(ReportTemplate $template)
    {
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, ReportTemplate $template)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'cover_title'     => 'nullable|string|max:255',
            'agency_name'     => 'nullable|string|max:255',
            'primary_color'   => ['required','string','regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required','string','regex:/^#[0-9a-fA-F]{6}$/'],
            'is_default'      => 'boolean',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($template->logo_path) Storage::disk('public')->delete($template->logo_path);
            $validated['logo_path'] = $request->file('logo')->store('templates/logos', 'public');
        }

        $existingSections = $template->layout_config_json['sections'] ?? [
            'cover','executive_summary','kpi_summary','position_chart',
            'distribution_chart','top_keywords','landing_pages',
            'competitor_monitoring','action_items',
        ];
        $validated['layout_config_json'] = ['sections' => $existingSections];

        if ($request->boolean('is_default')) {
            ReportTemplate::where('is_default', true)->where('id', '!=', $template->id)->update(['is_default' => false]);
        }

        unset($validated['logo']);
        $validated['is_default'] = $request->boolean('is_default');

        $template->update($validated);

        return redirect()->route('templates.index')->with('success', 'Template đã được cập nhật.');
    }

    public function destroy(ReportTemplate $template)
    {
        if ($template->generatedReports()->exists()) {
            return back()->withErrors(['delete' => 'Không thể xóa template đang được dùng bởi báo cáo.']);
        }
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template đã được xóa.');
    }
}
