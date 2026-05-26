<?php

namespace Weboldalnet\WebshopAiDefault\Http\Controllers\Admin\Webshop;

use App\Http\Controllers\Admin\AdminExtendedController;
use Weboldalnet\WebshopAiDefault\Models\WebshopProductLabel;
use Illuminate\Http\Request;

class WebshopProductLabelController extends AdminExtendedController
{
    public function index()
    {
        $labels = WebshopProductLabel::all();
        return view('admin.webshop.labels.index', compact('labels'));
    }

    public function create()
    {
        return view('admin.webshop.labels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bg_color' => 'required|string|max:20',
            'text_color' => 'required|string|max:20',
        ]);

        WebshopProductLabel::create($request->all());

        return redirect()->route('admin.webshop.labels.index')->with('success', 'Címke sikeresen létrehozva.');
    }

    public function edit(WebshopProductLabel $label)
    {
        return view('admin.webshop.labels.edit', compact('label'));
    }

    public function update(Request $request, WebshopProductLabel $label)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bg_color' => 'required|string|max:20',
            'text_color' => 'required|string|max:20',
        ]);

        $label->update($request->all());

        return redirect()->route('admin.webshop.labels.index')->with('success', 'Címke sikeresen frissítve.');
    }

    public function destroy(WebshopProductLabel $label)
    {
        $label->delete();
        return redirect()->route('admin.webshop.labels.index')->with('success', 'Címke sikeresen törölve.');
    }
}
