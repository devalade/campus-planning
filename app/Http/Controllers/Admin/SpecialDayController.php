<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialDayController extends Controller
{
    /**
     * List the special days.
     */
    public function index(): View
    {
        return view('admin.special-days', [
            'specialDays' => SpecialDay::query()->orderBy('date')->get(),
        ]);
    }

    /**
     * Add a special day.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'unique:special_days,date'],
            'label' => ['required', 'string', 'max:100'],
        ], [
            'date.required' => 'Choisissez une date.',
            'date.date_format' => 'La date est invalide.',
            'date.after_or_equal' => "La date doit être aujourd'hui ou plus tard.",
            'date.unique' => 'Ce jour spécial existe déjà.',
            'label.required' => 'Donnez un libellé au jour spécial.',
            'label.max' => 'Le libellé ne peut pas dépasser 100 caractères.',
        ]);

        SpecialDay::query()->create([
            ...$validated,
            'created_by' => $request->user()?->id,
        ]);

        return to_route('admin.special-days')->with('status', 'Jour spécial ajouté.');
    }

    /**
     * Delete a special day.
     */
    public function destroy(SpecialDay $specialDay): RedirectResponse
    {
        $specialDay->delete();

        return to_route('admin.special-days')->with('status', 'Jour spécial supprimé.');
    }
}
