<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\User;

class RegionController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $regions = Region::where('site_id', $siteId)->with('manager')->get();
        return view('admin.regions', compact('regions'));
    }

    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $data = $request->validate([
            'name' => 'required|string',
            'manager_id' => 'nullable|exists:master_users,id',
            'description' => 'nullable|string'
        ]);

        $data['site_id'] = $siteId;
        Region::create($data);

        return redirect()->back()->with('success', 'Região criada com sucesso!');
    }
}
